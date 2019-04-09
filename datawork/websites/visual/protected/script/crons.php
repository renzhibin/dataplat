<?php
error_reporting(E_ERROR|E_COMPILE_ERROR);
// 加载开发环境自定义配置文件
// defined('YII_DEBUG') or define('YII_DEBUG',false);
// change the following paths if necessary
//define('WEB_API', 'http://data.meiliworks.com:8181');
define('WEBROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('FRAMEWORK', WEBROOT . '/framework');

// Environment
require(dirname(__FILE__).'/../libs/env.php');

define('WEB_API', env('WEB_API'));
$yiic=FRAMEWORK . '/yii/yiic.php';
//$yiic=dirname(__FILE__).'/../framework/yiic.php';
$config=dirname(__FILE__).'/../config/console.php';
define('PHANTOMJS_SITE', env('PHANTOMJS_SITE'));
require_once($yiic);