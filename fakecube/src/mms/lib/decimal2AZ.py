#!/usr/bin/env python2.7
#coding=utf-8

'''
author: haiyuanhuang
'''

def _decimal26(num, lis):

    if num == 0:
        return

    div, mod = divmod(num, 26)
    if mod == 0:
        div -= 1
        lis.append(chr(90))
    else:
        lis.append(chr(mod+64))
    if div > 0:
        _decimal26(div, lis)

def decimal2AZ(num):
    '''
    将给定的数字，转换为26进制，并使用'A-Z'来表示。应用场景为类似excel的表头自动命名。
    '''
    num = int(num)
    lis = []
    
    _decimal26(num, lis)
    lis.reverse()
    return ''.join(lis)

# test
if '__main__' == __name__:

    while True:
        l = raw_input('enter a number: \n')
        print decimal2AZ(l)
