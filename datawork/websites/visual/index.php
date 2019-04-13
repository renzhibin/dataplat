<?php
/**
 * 项目入口文件
 *
 * @last    2018-04-09
 */
error_reporting(E_ALL ^ (E_NOTICE|E_WARNING));
date_default_timezone_set('PRC');

// 除非必要否则禁止更改
define('FRAMEWORK', '../../framework');
define('HOMEPAHT', dirname(__FILE__));

// Environment
require(dirname(__FILE__) . '/protected/libs/env.php');

// 除非必要否则禁止更改
define('YII_DEBUG', env('APP_DEBUG'));
define('XHPROF_ON', false);
define('JSVERSION', 3.0);
define('ALYAUTH', false);

// 引入自定义变量
// define('DEVELOP_API', 'http://developer.meiliworks.com');
define('PHANTOMJS_SITE', env('PHANTOMJS_SITE'));
define('WEB_API', env('WEB_API'));

// Yii start
require_once(FRAMEWORK . '/yii/yii.php');
Yii::setPathOfAlias('framework', FRAMEWORK);

$config = dirname(__FILE__) . '/protected/config/main.php';

$application = Yii::createWebApplication($config);
$application->run();
