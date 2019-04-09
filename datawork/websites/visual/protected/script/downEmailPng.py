#coding=utf-8
import urllib2
import os
import json
import pycurl
import StringIO
from hashlib import md5
from selenium import webdriver
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.keys import Keys
import os,time

def md5_file(name):
	m = md5()
	a_file = open(name, 'rb')    
	m.update(a_file.read())
	a_file.close()
	return m.hexdigest()

def uploadFile(filename):
	c = pycurl.Curl()   
	fp = StringIO.StringIO()
	c.setopt(pycurl.WRITEFUNCTION, fp.write)
	c.setopt(pycurl.FOLLOWLOCATION, 1)
	c.setopt(pycurl.MAXREDIRS, 5)
	c.setopt(pycurl.CONNECTTIMEOUT, 60) 
	c.setopt(pycurl.TIMEOUT, 300)
	c.setopt(c.POST, 1)
	c.setopt(c.URL, "http://data.meiliworks.com/protected/script/loadImg.php")  
	c.setopt(c.HTTPPOST, [("fileKey", (c.FORM_FILE, "/home/data/Downloads/"+filename))])
	c.perform()  
	c.close() 
	print filename,"has been uploaded"


if __name__ == '__main__':
	options = webdriver.ChromeOptions()
	options.add_experimental_option("excludeSwitches", ["ignore-certificate-errors"])
	driver = webdriver.Chrome('/home/data/xiaoyao/downPNG/chromedriver',chrome_options=options)
	response = urllib2.urlopen('http://data.meiliworks.com/timemail/urllibMail')
	html = response.readlines()
	for lines in html:
		js = json.loads(lines)
		if js["dataStatus"] == '1':
			filename = js['report_id']+'_'+time.strftime("%Y-%m-%d")+'.png'
			if (os.path.exists('/home/data/Downloads/' + filename)):
				print "the report_id " + filename + " has been downloaded!"
				continue
			print 'downloading report:',js['report_id']
			driver.get('http://data.meiliworks.com/report/showreport/'+js['report_id']+'?toDownPng=1');
			driver.add_cookie({'name':'down_png_request', 'value':md5_file('md5.key')})
			driver.get("http://data.meiliworks.com/report/showreport/"+js['report_id']+"?toDownPng=2");
			time.sleep(60)
			driver.find_element_by_id("downData").click()
			driver.find_element_by_id("png").click()
			driver.find_element_by_id("download").click()
			waits = 0
			while(not os.path.exists('/home/data/Downloads/' + filename)):
				time.sleep(3)
				if os.path.exists('/home/data/Downloads/'+filename):
					break
				print "wait for ",waits
				print '/home/data/Downloads/'+filename
				waits = waits + 1
				if wait > 10:
					break
			time.sleep(5)
			uploadFile(filename)
	driver.quit()
