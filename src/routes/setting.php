<?php

use App\Controller\SettingController;

$method = strtolower($_SERVER['REQUEST_METHOD']);
$route = $_GET['route'];
$params = explode('/', $route);
$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();

// Instancia del controlador de Usuario
$app = new SettingController($method, $route, $params, $data, $headers);

// Rutas
$app->getSetting('setting/');
$app->post('setting/');