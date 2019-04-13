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
$dotenv = new Dotenv(dirname(dirname(__DIR__)));
$dotenv->load();

