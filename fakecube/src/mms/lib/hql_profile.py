#!/usr/bin/env python2.7
# coding=utf-8
import os
import time
import random
import sys
import env as conf

"""
author: haiyuanhuang
date: 2014-11-07
为HQL提取基本的元信息：
    1. 指标
    2. 依赖的数据表
    3. 维度
    4. 维度组合

调用HQL_Profile.profile_from_hql方法
"""

import pyparsing

LEFT_CLOSE = "("
RIGHT_CLOSE = ")"
AST_STR_SPLIT = " "
DOT_SEPERATOR = "."
COMMA_SEPERATOR = ","

META_ROLE_COLOR_DICT = {
    "TABLE": "green",
    "METRIC": "yellow",
    "DIMENSION": "blue",
    "GROUPING_SETS": "red",
    "GROUPING__ID": "gray",
    "": "white"
}


def _duplicate_element(lis):
    for e in lis:
        if lis.count(e) != 1:
            return e
    return None


class HQL_Profile(object):
    def __init__(self):
        self.metrics = []  # list, all the metric names
        self.tables = []  # list, all the dependent hive tables
        self.dimensions = []  # list, all the "group by" keys
        self.grouping_sets = []  # list, all the "grouping sets"
        self.has_grouping__id = False  # indicate if the "grouping__id" appeared in the SELECT clause
        self.proper_pos = True  # does 'grouping__id' seperate the dimension-block and metric-block in the outside SELECT-CLAUSE
        self.dimensions_in_select = []
        self.status = 0  # 0 is good, others for bad
        self.report = ""  # if self.status is non-0, check this for why

    def __str__(self):
        m, t, d, g, ds = map(", ".join, (
            self.metrics, self.tables, self.dimensions, self.grouping_sets, self.dimensions_in_select))
        return '\n'.join(("metrics: " + m, "tables: " + t, "dimensions: " + d, "grouping_sets: " + g,
                          "has_grouping__id: " + str(self.has_grouping__id),
                          "grouping__id proper pos: " + str(self.proper_pos), "dimensions_in_select: " + ds))

    def _check(self):
        # duplication check
        list_name = ['metric', 'dimension in group by', 'grouping sets', 'dimension in select']
        for idx, lis in enumerate((self.metrics, self.dimensions, self.grouping_sets, self.dimensions_in_select)):
            d_ele = _duplicate_element(lis)
            if d_ele is not None:
                self.status = -1
                self.report = "Duplicated %s: %s" % (list_name[idx], d_ele)
                return

        # order check
        groupby_dim_num, select_dim_num = len(self.dimensions), len(self.dimensions_in_select)
        if groupby_dim_num != select_dim_num:
            self.status = -1
            self.report = "%d dimensions in 'SELECT-CLAUSE, but %d in 'GROUP BY-CLAUSE'." % (
                select_dim_num, groupby_dim_num)
            return
        for idx, dim in enumerate(self.dimensions):
            if dim != self.dimensions_in_select[idx]:
                self.status = -1
                self.report = "dimension order are different at position %d: 'SELECT-CLAUSE'(%s) and 'GROUP BY-CLAUSE'(%s)." % (
                    idx, self.dimensions_in_select[idx], dim)
                return


    def _profiling(self, ast_tree):
        self._profile_table(ast_tree)
        self._profile_metric(ast_tree)
        self._profile_dimension(ast_tree)
        self._profile_group_sets(ast_tree)
        self._check()

    def _profile_table(self, ast_tree):
        node = ast_tree
        if node.key == 'TOK_ALLCOLREF':
            for child in node.children:
                if child.key == "TOK_TABNAME":
                    pass
                else:
                    self._profile_table(child)


        else:
            if node.key == "TOK_TABNAME":
                node.role = "TABLE"
                self.tables.append(DOT_SEPERATOR.join(node.values))

            for child in node.children:
                self._profile_table(child)

    def _profile_metric(self, ast_tree):
        path = "TOK_QUERY.TOK_INSERT.TOK_SELECT"
        target = "TOK_SELEXPR"

        pathes = path.split(".")
        node = ast_tree
        assert node.key == pathes[0]

        # find the parent node of all the metrics
        for i in range(1, len(pathes)):
            for child in node.children:
                if child.key == pathes[i]:
                    node = child
                    break

        # gathering all the metrics
        for child in node.children:
            if child.key == target:

                if len(child.values) == 0:
                    # grouping__id or dimensions
                    for c in child.children:
                        if len(c.values) == 0:
                            self.status = 1
                            self.report = "[SELECT] Empty colunm"
                        elif len(c.values) == 1:
                            if c.values[0].lower() == 'grouping__id':
                                c.role = "GROUPING__ID"
                                self.has_grouping__id = True
                            else:
                                # dimensions in select clause
                                self.dimensions_in_select.append(c.values[0])
                                # when we has already met the
                                # 'grouping__id', we shouldn't meet
                                # dimensions again
                                if self.proper_pos and self.has_grouping__id:
                                    self.proper_pos = False
                        else:
                            # dimensions in select clause
                            self.dimensions_in_select.append(DOT_SEPERATOR.join(c.values))
                else:
                    # only metric can have an alias
                    # every metric must have an alias
                    # and this also means, non-metric in select-clause should never has an alias
                    # 白名单函数 在白名单里的成为维度
                    dim_flag=False
                    for c in child.children:
                        if c.values and c.values[0] in ['substr']:
                            self.dimensions_in_select.append(DOT_SEPERATOR.join(child.values))
                            dim_flag=True
                            #print '------','key',c.key,'chilid',str(c.children),'value',c.values,'role',c.role
                    if dim_flag==False:
                        child.role = "METRIC"
                        self.metrics.append(DOT_SEPERATOR.join(child.values))
            else:
                # this could be a bug?
                pass

    def _profile_dimension(self, ast_tree):
        path = "TOK_QUERY.TOK_INSERT.TOK_GROUPING_SETS"
        target = "TOK_TABLE_OR_COL"

        pathes = path.split(".")
        node = ast_tree
        assert node.key == pathes[0]

        # find the parent node of all the dimensions
        for i in range(1, len(pathes)):
            for child in node.children:
                if child.key == pathes[i]:
                    node = child
                    break

        # find all the dimensions
        for child in node.children:
            if child.key == target:
                child.role = "DIMENSION"
                self.dimensions.append(DOT_SEPERATOR.join(child.values))

    def _profile_group_sets(self, ast_tree):
        path = "TOK_QUERY.TOK_INSERT.TOK_GROUPING_SETS"
        target1 = "TOK_GROUPING_SETS_EXPRESSION"
        target2 = "TOK_TABLE_OR_COL"

        pathes = path.split(".")
        node = ast_tree
        assert node.key == pathes[0]

        # find the parent node of all the grouping_sets
        for i in range(1, len(pathes)):
            for child in node.children:
                if child.key == pathes[i]:
                    node = child
                    break

        # find all the grouping sets
        for child in node.children:
            if child.key == target1:
                g_sets = []
                for c in child.children:
                    if c.key == target2:
                        if len(c.values) == 0:
                            g_sets.append("")
                        else:
                            g_sets.append(DOT_SEPERATOR.join(c.values))
                child.role = "GROUPING_SETS"
                self.grouping_sets.append("(" + COMMA_SEPERATOR.join(g_sets) + ")")


class Node(object):
    def __init__(self, operator_name):
        self.key = operator_name
        self.values = []  # a list of string
        self.children = []  # AST elements
        self.role = ""  # which role does current node represent, for later coloring

    def __str__(self):
        temp = [self.key, ]
        for child in self.children:
            temp.append(str(child))
        for v in self.values:
            temp.append(v)
        temp_str = AST_STR_SPLIT.join(temp)
        return LEFT_CLOSE + temp_str + RIGHT_CLOSE


def get_ast(hql,attach=''):
    import commands

    if not os.path.exists(conf.TMP_SQL_PATH):
        os.mkdir(conf.TMP_SQL_PATH)

    now_time=time.strftime("%Y_%m_%d_%H_%M_%S",time.localtime(time.time()))
    random_num = random.randrange(0,10000)

    tmp_sql_file = '%s/tmp_%s_%s.sql' %(conf.TMP_SQL_PATH,now_time,random_num)
    write_hql = 'explain EXTENDED %s' %hql
    if attach !='':
        ending = attach[-1]
        if ending != ';':
            attach +=';'
        write_hql = '%s %s' % (attach, write_hql)

    with open(tmp_sql_file,'w') as f:
        f.write(write_hql)

    cmd = "hive -f %s" %tmp_sql_file

    status, output = commands.getstatusoutput(cmd)

    if status != 0:
        retu = output
    else:
        os.remove(tmp_sql_file)
        import re

        r = re.compile(r'ABSTRACT SYNTAX TREE:\n+(.*?)\n+STAGE DEPENDENCIES:', re.DOTALL)
        res = r.findall(output)
        retu = str(res[0].strip())
    return status, retu


def profile_from_hql(hql, filename=None, type='hql',hql_type=1,attach=''):
    assert len(hql) != 0
    ast_str = hql
    if type == 'hql':
        #from ast_checker import AstChecker
        #ac = AstChecker()
        #ast_str=ac.get_ast(hql)
        status, ast_str = get_ast(hql,attach)

        if status != 0:
            return 1, ast_str
        if int(hql_type) !=1:
            return status,'success'
    profile_msg = _profile_from_ast(ast_str, filename)[0]
    msg = ''
    status = 0
    if profile_msg.status != 0:
        msg = profile_msg.report
        status = 1
    else:
        msg = profile_msg
        status = 0
    return status, msg


def _construct_dot(syntax_node, dot, dot_node):
    for child in syntax_node.children:
        c = repr(child)
        label = child.key
        if len(child.values) > 0:
            label += "(" + '.'.join(child.values) + ")"

        dot.add_node(c, label=label, style="filled", fillcolor=META_ROLE_COLOR_DICT[child.role])
        dot.add_edge(dot_node, c)
        _construct_dot(child, dot, c)


def _draw_ast(ast_tree, filename=None):
    try:
        import pygraphviz as pgv
    except:
        return False

    G = pgv.AGraph(strict=True, directed=True)
    root_label = ast_tree.key
    if len(ast_tree.values) > 0:
        root_label += "(" + '.'.join(ast_tree.values) + ")"

    root_name = repr(ast_tree)
    G.add_node(root_name, label=root_label, style="filled", fillcolor=META_ROLE_COLOR_DICT[ast_tree.role])
    _construct_dot(ast_tree, G, root_name)
    G.layout(prog='dot')
    G.draw(filename)
    return True


ne = pyparsing.nestedExpr(LEFT_CLOSE, RIGHT_CLOSE)  # using the default seperator "()"


def _profile_from_ast(ast_str, filename=None):
    assert ast_str is not None
    assert len(ast_str) != 0

    nested_list = ne.parseString(ast_str).asList()
    assert nested_list is not None
    assert len(nested_list) == 1

    ast_tree = _construct_ast(nested_list[0])
    profile = HQL_Profile()
    profile._profiling(ast_tree)

    draw_flag = False
    if filename is not None:
        draw_flag = _draw_ast(ast_tree, filename)

    return profile, draw_flag


def _construct_ast(nested_list):
    root = None
    for idx, ele in enumerate(nested_list):

        if idx == 0:
            # root
            assert isinstance(ele, str)
            root = Node(ele)
        else:
            if isinstance(ele, str):
                if ele.startswith("TOK_"):
                    # "TOK_" must be a node itself
                    ele = [ele, ]
                else:
                    root.values.append(ele)
                    continue
            if isinstance(ele, list):
                node = _construct_ast(ele)
                root.children.append(node)
            else:
                raise Exception("Unexpected type:[%s] in AST." % type(ele))
    return root
