
from log4me import MyLogger
logger = MyLogger.getLogger()

def _check_test(hql, params):
    return True, "OK"


import re
# paste this regular expression into https://www.debuggex.com/ to check the pattern
select_p = re.compile(r"\s*(SELECT|select)\s+(?P<dim1>.+),\s*(GROUPING__ID|grouping__id)\s*(?P<metric>(,\s*(.*)\s+(AS|as)\s+(\w+)\s*)+)(FROM|from)(.*)\s+(group by|GROUP BY)\s+(?P<dim2>([\w\s,]+)+)\s+(GROUPING SETS|grouping sets)\s*\((?P<gsets>.*)\)\s*", re.DOTALL)
metric_p = re.compile(".*\s+(AS|as)\s+(\w+)")
table_p = re.compile("\s+(FROM|from)\s+([\w.]+)")

def _check_regex(hql, params):

    # here, using "match" to make sure the whole hql satisfy our pattern
    m = select_p.match(hql)
    if m is None:
        return False, "The given HQL is not supported"

    # useless chars
    garbage = ", \t\n\r"

    # the "group" keys in select
    dim1_str = m.group("dim1").strip(garbage)
    dim1_eles = [dim.strip() for dim in dim1_str.split(',')]

    # the target metrics
    metric_str = m.group("metric").strip(garbage)
    single_metric_m = metric_p.findall(metric_str)
    if not single_metric_m: # None or with 0-length
        return False, "No metric output"
    metric_eles = [single_metric[1] for single_metric in single_metric_m]

    # tables, all the tables refered in the HQL will be extracted
    single_table_m = table_p.findall(hql)
    if not single_table_m: # None or with 0-length
        return False, "No table found in the HQL"
    table_eles = [single_table[1] for single_table in single_table_m]

    # the "group by"
    dim2_str = m.group("dim2").strip(garbage)
    dim2_eles = [dim.strip() for dim in dim2_str.split(',')]

    # the "grouping sets"
    # TODO parsing "grouping sets" is a hard work, maybe pyparsing is good
    gsets_str = m.group("gsets").strip(garbage)

    logger.debug("dim1_eles:%s"%dim1_eles)
    logger.debug("metric_eles:%s"%metric_eles)
    logger.debug("tables:%s"%table_eles)
    logger.debug("dim2_eles:%s"%dim2_eles)
    #logger.debug("gsets_eles:%s"%gsets_eles)

    # get the description about the hql from yaml...
    # the vars are all started with "d_"
    d_metric_group_json = params["metric_group_json"]
    d_metrics = set([e["name"] for e in d_metric_group_json["metrics"]])
    d_dims = set([e["name"] for e in d_metric_group_json["dimensions"]])
    d_tables = set([e["name"] for e in d_metric_group_json["tables"]])
    d_gsets = set([e["name"] for e in d_metric_group_json["dim_sets"]])

    logger.debug("d_metric:%s"%d_metrics)
    logger.debug("d_dim:%s"%d_dims)
    logger.debug("d_tables:%s"%d_tables)
    logger.debug("d_gsets:%s"%d_gsets)

    # check metric
    d_metrics_count = len(d_metrics)
    metrics_count = len(metric_eles)
    if d_metrics_count != metrics_count:
        return False, "%d metrics defined but %d in HQL."%(d_metrics_count, metrics_count)
    for metric in metric_eles:
        if metric not in d_metrics:
            return False, "Metric[%s] is not defined."%(metric)

    # check dim
    d_dims_count = len(d_dims)
    dims_count = len(dim2_eles)
    if d_dims_count != dims_count:
        return False, "%d dims defined but %d in HQL."%(d_dims_count, dims_count)
    for dim in dim2_eles:
        if dim not in d_dims:
            return False, "Dim[%s] is not defined."%dim

    # check table
    d_tables_count = len(d_tables)
    tables_count = len(table_eles)
    for table in table_eles:
        if table not in d_tables:
            return False, "Table[%s] is not defined."%table

    # check grouping sets
    """
    d_gsets_count = len(d_gsets)
    gsets_count = len(gsets_eles)
    if d_gsets_count != gsets_count:
        return False, "%d grouping sets defined but %d in HQL."%(d_gsets_count, gsets_count)
    for gset in gsets_eles:
        if gset not in d_gsets:
            return False, "Grouping sets[%s] is not defined."%gset
    """


    return True, "OK"

def _check(hql, params):
    #return _check_test(hql, params)
    return True, "OK"
    return _check_regex(hql, params)

def checkHQL(genHQLFunc):
    def aopFunc(self, params, test):
        hql = genHQLFunc(self, params, test)
        check_flag, check_info = _check(hql, params)
        return hql, check_flag, check_info
    return aopFunc
