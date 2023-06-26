<?php

use App\Controller\CompanyController;

$method = strtolower($_SERVER['REQUEST_METHOD']);
$route = $_GET['route'];
$params = explode('/', $route);
$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();

// Instancia del controlador de Usuario
$app = new CompanyController($method, $route, $params, $data, $headers);

// Rutas
$app->getAll('company/');
$app->post('company/');
$app->getById("company/{$params[1]}");

$app->enable("company/enable/{$params[2]}");
$app->disable("company/disable/{$params[2]}");
$app->delete("company/delete/{$params[2]}");