<?php
Yii::setPathOfAlias('framework', '../../../framework/');
$config = array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'../',
    'timeZone' => 'Asia/Shanghai',
    'name'=>'visual',
    // preloading 'log' component
    'preload'=>array('log','user'),
    // autoloading model and component classes
    'import'=>array(
        'framework.components.*',
        'framework.extensions.*',
        'framework.extensions.smarty.sysplugins.*',
        'framework.extensions.yii-mail.*',
        'framework.extensions.libchart.*',
        'framework.extensions.Jpgraph.*',
        'application.models.*',
        'application.components.*',
        'application.managers.*',
        'application.extensions.*',
    ),
    'modules'=>array(

    ),
    // application components
    'components'=>array(
        //curl
        'curl' => array(
            'class' => 'Curl',
            'options' => array()
        ),
        'session' => array(
            'autoStart'=>false,
            'class'=>'CCacheHttpSession',
            'sessionName'=>'visual',
            'cacheID'=>'sessionCache',
            'cookieMode'=>'only',
            //'timeout' => 7200,
            //'cookieParams' => array('domain' => "focus.meiliworks.com"),
        ),
        'sessionCache' => array(
            'class'=>'CRedisCache',
            'hostname'=>env('REDIS_HOST_NAME', null),
            'password'=>env('REDIS_PASSWORD', null),
            'port'=>env('REDIS_PORT', 6379),
            'database'=>env('REDIS_DATABASE', 0),
        ),
        'cache'=>array(
            'class'=>'CRedisCache',
            'hostname'=>env('REDIS_HOST_NAME', null),
            'password'=>env('REDIS_PASSWORD', null),
            'port'=>env('REDIS_PORT', 6379),
            'database'=>env('REDIS_DATABASE', 0),
         ),
        // uncomment the following to enable URLs in path-format
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName' => false,
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
        ),
        'user'=>array(
            // enable cookie-based authentication
            'class'=>'User',
            'allowAutoLogin'=>true,
        ),
        'errorHandler'=>array(
            // use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'trace,info,error, warning',
                ),
                // uncomment the following to show log messages on web pages
                array(
                    'class' => 'CWebLogRoute',
                    'enabled' => YII_DEBUG,
                    'levels' => 'error, warning, trace,info',
                    'categories' => 'application',
                    'showInFireBug' => true,
                ),
            ),
        ),
        'smarty'=>array(
            'class'=>'framework.extensions.CSmarty',
        ),
        'mail' => array(
            'class' => 'framework.extensions.yii-mail.YiiMail',
            'transportType'=>'smtp',
            'viewPath' => 'application.views.mail',
        ),
    ),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(  
        'adminEmail'        => 'info@example.com',
        'home'              => 'http://focus.meiliworks.com/',
        'htDomain'          => 'http://works.meiliworks.com/',
        'globalDomain'      => 'meiliworks.com'
    ),
);

$database = require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'db.php');  
if (!empty($database)) {  
    $config['components'] = CMap::mergeArray($config['components'],$database);  
    //Yii::app()->setComponents($database);
}

if(function_exists("focus_load_local_config")) {
    $config = focus_load_local_config($config);
}

return $config;