<?php

use App\Config\ErrorLog;
use App\Config\ResponceHttp;

require_once dirname(__DIR__) . '/vendor/autoload.php';

ResponceHttp::headerHttpDev($_SERVER['REQUEST_METHOD']);
ErrorLog::activateErrorLog();

if (isset($_GET['route'])) {

    $url = explode("/", $_GET['route']);
    // error_log($url[0]);
    $list = [
        'auth', 
        'company', 
        'procedure',
        'user',
        'userType',
        'employee',
        'setting', 
        'country',
        'branch'
    ];
    $file = dirname(__DIR__) . '/src/routes/' . $url[0] . '.php';

    if (!in_array($url[0], $list)) {
        echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,'La ruta no existe'));
        exit;
    }

    if (is_readable($file)) {

        require $file;
        exit;
    } else {
        echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,'La ruta no existe'));
        exit;
    }
} else {
    echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,'La ruta no existe'));
    exit;
}
