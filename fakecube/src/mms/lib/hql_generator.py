#!/usr/bin/env python2.7
#coding=utf-8

import re
from abc import ABCMeta, abstractmethod
import hql_checker

class HQLGenerator(object):

    __metaclass__ = ABCMeta

    @abstractmethod
    def __init__(self):
        self._HQL_template = "" # the hql template
        self._test_HQL_template = "" # the test hql tempalte, this could limit the input

        # you can debug this regular expression on https://www.debuggex.com/
        self._hql_p = re.compile(r"\s*select\s+(.+)\s+from.*\s+group\s+by\s+(.+)")

    @abstractmethod
    @hql_checker.checkHQL
    def genHQL(self, params, test=False):
        """
        sub-classes must override this method!!!
        And must add the same decorator!!!

        use the give param to construct the hql string.
        Return a tuple (hql, valid_flag, valid_info), if the valid_flag is True,
        then OK, else the construction sucks, and the valid_info gives the reason why.

        param: a dict, use the kv to construct the hql.
        test: a boolean arg, use the test hql template if set True
        """
        assert isinstance(params, dict)

        hql = ""

        if test:
            hql = self._test_HQL_template
        else:
            hql = self._HQL_template

        return hql


# unit test
if "__main__" == __name__:

    import yaml

    yaml_str = open("../conf/mob_app_metric.yaml", "r").read()
    json = yaml.load(yaml_str)

    metric_groups = json["mob_app_metrics"]["categories"][0]["groups"]
    G = HQLGenerator()
    params = {"metric_group_json":metric_groups[0], "dt":"2014-08-20"}
    print G.genHQL(params)
