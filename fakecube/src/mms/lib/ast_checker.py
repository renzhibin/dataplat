#!/usr/local/bin/python
#encoding:utf8

import re,os,commands,sys
from pyparsing import nestedExpr
from pprint import pprint
from hive_api import HiveServerClient 


class AstChecker(object):

    def __init__(self):
        self.client = HiveServerClient()

    def __del__(self):
        self.client.close()
    def get_ast(self,hql):
        return self.__get_ast(hql)
    def check_hql(self,hql,group):
        flag = True
        err_str = ""
        ret = self.parse_ast(hql)

        if ret is None:
            flag = False
            err_str = "function __get_dast() in class AstChecker return empty!"
            return flag,err_str

        # check metrics by compare two setunordered
        ms = set()
        for i in group["metrics"]:
            ms.add(i["name"].strip())
        ms2 = set(ret["metrics"])

        ms2.discard("GROUPING__ID")
        ms2 = ms2 - set(ret["dimensions"])
        if ms2 != ms :
            flag = False
            me = list(ms ^ ms2)
            err_str += "The metrics mismatched: %s; " % (",".join(me))
            
        # check dimensions by compare two set unordered 
        gs = set() 
        for j in group["dimensions"]:
            gs.add(j["name"].strip())
        gs2 = set(ret["dimensions"]) 
        if gs2 != gs :
            flag = False
            ge = list(gs ^ gs2)
            err_str += "The dimensions mismatched: %s; " % (",".join(ge))

        # check grouping sets by compare two sets unordered
        ds = set()
        for m in group["dim_sets"]:
           mn = m["name"].strip("()").split(",")
           mn2 = []
           for d in mn:
               mn2.append(d.strip())
           dim_set = "(" + ",".join(mn2) + ")"
           ds.add(dim_set) 
        ds2 = set(ret["dim_sets"])
        if ds2 != ds :
            flag = False
            de = list(ds ^ ds2)
            err_str += "The dim_sets mismatched: %s; " % (",".join(de))

        # check tables by compare two setsunordered
        ts = set()
        for n in group["tables"]:
            ts.add(n["name"].strip())
        ts2 = set(ret["tables"]) 
        if ts2 != ts :
            flag = False
            te = list(ts ^ ts2)
            err_str += "The tables mismatched: %s; " % (",".join(te))

        return flag,err_str
    
    def __get_ast(self,hql):
        
        hql = "explain %s" % (hql)
        res, schema = self.client.execute(hql)
        
        '''
        cmd = "hive -e \"explain %s\"" % (hql)
        print cmd
        (status, output) = commands.getstatusoutput(cmd)
        ast = ""
        '''
        #todo 
        try:
            for i in range(len(res)):
                if res[i][0].strip() == "ABSTRACT SYNTAX TREE:":
                    ast = res[i+1][0].strip()
                    break
        except:
            print >> sys.stderr, "Fatal : get_ast() sucks!"
            pass
        return ast

    def __parse_from(self,ast_list,tbs):

        a = ast_list
        
        if a[0] == "TOK_TABNAME":
            # by haiyuanhuang, dm.dm_order_4analyst
            tbs.append('.'.join(a[1:]))
            #tbs.append(a[1])
        else:
            for i in a[1:]:
                if isinstance(i,list):
                    if i[0] != "TOK_ALLCOLREF":
                        self.__parse_from(i,tbs)

    def parse_ast(self,hql):
        
        # catch exception
        ast = self.__get_ast(hql)
        if ast =='':
            print 'ast is empty!'
            return 
          
        result =  nestedExpr("(", ")").parseString(ast)
        aa = result.asList()[0]
       
        res = {}
        if aa[0] != "TOK_QUERY":
            return res
        
        res["tables"] = []
        res["conditions"] = []
        res["dimensions"] = []
        res["metrics"] = []
        res["dim_sets"] = []
        res["with_rollup"] = []
        
        for i in aa[1:]:
            #
            if i[0] == "TOK_FROM":
                for j in i[1:]:
                    if isinstance(j,list):
                        self.__parse_from(j,res["tables"])

            #
            if i[0] == "TOK_INSERT":
                for j in i:
                    if j[0] == "TOK_SELECT":
                        for m in j[1:]:
                            if m[0] == "TOK_SELEXPR":
                                if len(m) > 2:
                                    res["metrics"].append(m[2])
                                    continue
                                for n in m[1:]:
                                    if n[0] == "TOK_TABLE_OR_COL":
                                        res["metrics"].append(n[1])
                                    if n[0] == "TOK_FUNCTIONSTAR":
                                        res["metrics"].append(n[1]+"(*)")
                    #elif j[0] == "TOK_WHERE":
                    elif j[0] == "TOK_GROUPBY":
                        for m in j[1:]:
                            if m[0] == "TOK_TABLE_OR_COL":
                                res["dimensions"].append(m[1])
                    elif j[0] == "TOK_ROLLUP_GROUPBY":
                        for m in j[1:]:
                            if m[0] == "TOK_TABLE_OR_COL":
                                res["with_rollup"].append(m[1])
                    elif j[0] == "TOK_GROUPING_SETS":
                        for m in j[1:]:
                            if m[0] == "TOK_TABLE_OR_COL":
                                res["dimensions"].append(m[1])
                            if m[0] == "TOK_GROUPING_SETS_EXPRESSION":
                                s = []
                                for n in m[1:]:
                                    if n[0] == "TOK_TABLE_OR_COL":
                                        s.append(n[1])
                                gs = "(" + ",".join(s) + ")"
                                res["dim_sets"].append(gs)
                            if m == "TOK_GROUPING_SETS_EXPRESSION":
                                res["dim_sets"].append("()")
        return res
     
    def parse_ast2(self,hql):
        
        #catch exception

        #
        ast = self.__get_ast(hql)
        result =  nestedExpr("(", ")").parseString(ast)
        aa = result.asList()[0]
       
        res = {}
        if aa[0] != "TOK_QUERY":
            return res
        
        res["tables"] = []
        res["conditions"] = []
        res["dimensions"] = []
        res["metrics"] = []
        res["dim_sets"] = []
        res["with_rollup"] = []
        
        for i in aa[1:]:
            #
            if i[0] == "TOK_FROM":
                for j in i[1:]:
                    if isinstance(j,list):
                        self.__parse_from(j,res["tables"])
            #
            if i[0] == "TOK_INSERT":
                for j in i:
                    if j[0] == "TOK_SELECT":
                        for m in j[1:]:
                            if m[0] == "TOK_SELEXPR":
                                if len(m) > 2:
                                    res["metrics"].append(m[2])
                                    continue
                                for n in m[1:]:
                                    if n[0] == "TOK_TABLE_OR_COL":
                                        res["metrics"].append(n[1])
                                    if n[0] == "TOK_FUNCTIONSTAR":
                                        res["metrics"].append(n[1]+"(*)")
                    #elif j[0] == "TOK_WHERE":
                    elif j[0] == "TOK_GROUPBY":
                        for m in j[1:]:
                            if m[0] == "TOK_TABLE_OR_COL":
                                res["dimensions"].append(m[1])
                    elif j[0] == "TOK_ROLLUP_GROUPBY":
                        for m in j[1:]:
                            if m[0] == "TOK_TABLE_OR_COL":
                                res["with_rollup"].append(m[1])
                    elif j[0] == "TOK_GROUPING_SETS":
                        for m in j[1:]:
                            if m[0] == "TOK_TABLE_OR_COL":
                                res["dimensions"].append(m[1])
                            if m[0] == "TOK_GROUPING_SETS_EXPRESSION":
                                s = []
                                for n in m[1:]:
                                    if n[0] == "TOK_TABLE_OR_COL":
                                        s.append(n[1])
                                gs = "(" + ",".join(s) + ")"
                                res["dim_sets"].append(gs)
                            if m == "TOK_GROUPING_SETS_EXPRESSION":
                                res["dim_sets"].append("()")
        #
  
        return res

def main():
    #test
    d = "select client_device,count(*) from (select b.* from mobile_app_log_new_orc b join mobile_app_log_new_orc c) a WHERE dt='2014-08-29' group by client_device"
    ac = AstChecker()
    res = ac.parse_ast(d)
    #output
    table = ",".join(res["tables"]) 

    dimensions = ",".join(res["dimensions"])
    if len(res["with_rollup"]) > 0:
        dimensions = ",".join(res["with_rollup"]) + " with rollup"
    if len(res["dim_sets"]) > 0:
        dimensions = dimensions + " grouping sets (" + ",".join(res["grouping_sets"]) + ")"
    measures = ",".join(res["metrics"])
    conditions = "1"
    if len(res["conditions"]) > 0:
        condition = condition + " and " + ",".join(res["conditions"])

    sql = "select %s from %s where %s group by %s " % (measures,table,conditions,dimensions)
    print sql

if __name__ == "__main__":
    # 
    main()






