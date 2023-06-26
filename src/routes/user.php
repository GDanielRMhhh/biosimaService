<?php

use App\Controller\UserController;

$method = strtolower($_SERVER['REQUEST_METHOD']);
$route = $_GET['route'];
$params = explode('/', $route);
$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();

// Instancia del controlador de Usuario
$app = new UserController($method, $route, $params, $data, $headers);

// Rutas
$app->getAll('user/');
$app->post('user/');
$app->getUser("user/{$params[1]}");

$app->enable("user/enable/{$params[2]}");
$app->disable("user/disable/{$params[2]}");
$app->delete("user/delete/{$params[2]}");