#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
import re

class MmsHqlParse(object):
    def __init__(self):
        pass



def get_hql_tables(hql=''):
    table_eles=[]
    if not hql:
        return table_eles
    table_p = re.compile("\s+(FROM|from|join|JOIN)\s+([\w.]+)")
    single_table_m = table_p.findall(hql)
    if not single_table_m:
        return table_eles
    table_eles = [single_table[1] for single_table in single_table_m]
    '''
    table_p = re.compile(r'\/\**\s*([\w.]+)\s*\**\/')
    p_res=table_p.findall(hql)
    for e in p_res:
        if e.strip():
            table_eles.append(e.strip())
    table_eles=list(set(table_eles))
    '''
    return table_eles



