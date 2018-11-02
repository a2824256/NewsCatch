<?php
use Beanbun\Lib\Db;

require_once(__DIR__ . '/vendor/autoload.php');
Db::$config['spider'] = [
    'server' => '127.0.0.1',
    'port' => '3306',
    'username' => 'root',
    'password' => 'newlife',
    'database_name' => 'spider',
    'database_type' => 'mysql',
    'charset' => 'utf8',
];