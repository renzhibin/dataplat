#!/usr/bin/env python2.7
#coding=utf-8
import fcntl
import sys
import env as conf


class SingletonLock:
    
    def __init__(self, lock_path=conf.LOCK_PATH+"singleton.lock"):
        self.lock_file = lock_path
        self.fp = open(lock_path,'w')
 
    def ex_lock(self):
        fcntl.flock(self.fp, fcntl.LOCK_EX)

    def nb_lock(self):
        fcntl.flock(self.fp,fcntl.LOCK_NB | fcntl.LOCK_EX )

    def sh_lock(self):
        fcntl.flock(self.fp, fcntl.LOCK_SH)
 
    def unlock(self):
        fcntl.flock(self.fp, fcntl.LOCK_UN)
 
    def __del__(self):
        try:
            self.fp.close()
            #os.remove(self.filename)
        except:
            pass

