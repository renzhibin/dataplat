<?php

$lines = include_once __DIR__ . DIRECTORY_SEPARATOR . 'database.php';

$configs = array();

function parse(&$configs, $lines, $merge=true, $direct=false){
    foreach($lines['connections'] as $line) {
        $dbname   = $line['db'];
        $hostname = $line['host'];
        $port     = $line['port'];
        $user     = $line['user'];
        $pass     = $line['pass'];
        $ismaster = $line['master'] === 1;
        $suffix   = empty($line['suffix']) ? '' : "_{$line['suffix']}";

        $config = array(
            'connectionString' => "mysql:host={$hostname};port={$port};dbname={$dbname}",
            'emulatePrepare' => true,
            'username' => $user,
            'password' => $pass,
            'charset' => 'utf8'
        );

        if($ismaster) {
            $configs["db_{$dbname}{$suffix}"] = $config;
            $configs["db_{$dbname}{$suffix}"]["class"] = "CDbConnection";
        } else {
            $config['port'] = $port;
            $config['host'] = $hostname;
            $config['db'] = $dbname;
            $config['database'] = "sdb_{$dbname}{$suffix}";
            $config['direct'] = $direct;
            
            $configs["sdb_{$dbname}{$suffix}"]["class"] = "DbConnection";
            
            if ($merge) {
                $configs["sdb_{$dbname}{$suffix}"]["dbs"][] = $config;
            } else {
                $configs["sdb_{$dbname}{$suffix}"]["dbs"] = array($config);
            }
        }
    }
}

parse($configs, $lines);
/*
if (!defined('OTHER_HOST')) {
    return $configs;
}
$dbName = '';
$iniFile = '';
switch (OTHER_HOST) {
    case SURVEY_HOST:
        $iniFile=dirname(__FILE__)."/local/mysql.ini.survey";
        $dbName = 'metric_meta_survey';
        break;
    default:
break;
}
if ($iniFile == '') {
    return $configs;
}
$reparseConfigFileContent = file($iniFile);
$reparseConfig = [];
parse($reparseConfig, $reparseConfigFileContent);
$masterConfigDbName = 'db_' . $dbName;
$slaveConfigDbName = 'sdb_' . $dbName;
if(array_key_exists($masterConfigDbName, $reparseConfig) and array_key_exists($slaveConfigDbName, $reparseConfig)) {
    $configs['sdb_metric_meta']=$reparseConfig[$slaveConfigDbName];
    $configs['db_metric_meta']=$reparseConfig[$masterConfigDbName];
}
*/
return $configs;
