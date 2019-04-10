<?php
/**
 * Require helpers
 */
require_once(__DIR__ . '/helpers.php');
/**
 * Load application environment from .env file
 */
// 引入 phpdotenv
require_once(FRAMEWORK . '/yii/phpdotenv/Dotenv.php');
require_once(FRAMEWORK . '/yii/phpdotenv/Loader.php');
require_once(FRAMEWORK . '/yii/phpdotenv/Validator.php');
$hostName = $_SERVER['SERVER_NAME'];
$envFile = '.env';
switch ($hostName) {
    case 'dt.xiaozhu.com':
        break;
    #case 'dh.xiaozhu.com':
    case 'dh.xiaozhu.com':
        $envFile = '.envDh';
        break;
    default:
        break;
}
$dotenv = new Dotenv(dirname(dirname(__DIR__)), $envFile);
$dotenv->load();

