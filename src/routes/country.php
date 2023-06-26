<?php

use App\Controller\CountryController;
use App\Controller\UserController;
use App\Model\CountryModel;

$method = strtolower($_SERVER['REQUEST_METHOD']);
$route = $_GET['route'];
$params = explode('/', $route);
$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();

// Instancia del controlador de Usuario
$app = new CountryController($method, $route, $params, $data, $headers);

// Rutas
$app->getAll('country/');
// $app->getUser("country/{$params[1]}");
// $app->post('country/');
// $app->deleteUser("country/");