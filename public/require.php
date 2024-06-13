<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = dirname(dirname(__FILE__));

// Env file
$env = (object) parse_ini_file('.env');

// RedBean
include "./vendor/autoload.php";
use \RedBeanPHP\R as R;

R::setup('mysql:host='.$env->DB_HOST.';dbname='.$env->DB_DATABASE, $env->DB_USERNAME, $env->DB_PASSWORD);

$autoload = glob(__DIR__ . "/Classes/*.php");
foreach($autoload as $class) {
    require_once($class);
}