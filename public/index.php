<?php

use App\Config\ErrorLog;
use App\Config\ResponceHttp;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// const POSITION = 0; //Develop
const POSITION = 1; //Production

// ResponceHttp::headerHttpPro($_SERVER['REQUEST_METHOD'],$_SERVER['HTTP_ORIGIN']);
ResponceHttp::headerHttpDev($_SERVER['REQUEST_METHOD']);
ErrorLog::activateErrorLog();

// Ruta raíz de bienvenida
$currentRoute = $_GET['route'];
$routeVariables = ['public', '/public', 'public/', '/public/'];

if (in_array($currentRoute, $routeVariables)) {
    header('Content-Type: text/html');
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Servicio funcionando correctamente</title>
        <style>
            body {
                background-color: #008080; /* Color de fondo alusivo al logo (verde y azul) */
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }

            .container {
                text-align: center;
            }

            h1 {
                color: #FFFFFF; /* Color del título (blanco) */
                font-family: Arial, sans-serif;
                font-size: 24px;
                margin-top: 20px;
                margin-bottom: 0;
            }

            img {
                max-width: 100%;
                max-height: 100%;
                display: block;
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Servicio funcionando correctamente</h1>
            <img src="https://biosima.org/images/logo.png" alt="Logo de Biosima">
        </div>
    </body>
    </html>';
    exit;
}

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

    $file = '';
    if (isset($url[POSITION])) {
        $file = dirname(__DIR__) . '/Src/Routes/' . $url[POSITION] . '.php';
    } else {
        echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, 'La ruta no existe 1'));
        exit;
    }

    if (!in_array($url[POSITION], $list)) {
        echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, 'La ruta no existe 2'));
        exit;
    }

    if (is_readable($file)) {
        require $file;
        exit;
    } else {
        echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, 'La ruta no existe 3'));
        exit;
    }
} else {
    echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, 'La ruta no existe 4'));
    exit;
}
