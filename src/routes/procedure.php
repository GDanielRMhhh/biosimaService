<?php

use App\Controller\ProcedureController;

$method = strtolower($_SERVER['REQUEST_METHOD']);
$route = $_GET['route'];
$params = explode('/', $route);
$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();

// Instancia del controlador de Usuario
$app = new ProcedureController($method, $route, $params, $data, $headers);

// Rutas
$app->getAll('procedure/');
$app->post('procedure/');
$app->getById("procedure/{$params[1]}");

$app->enable("procedure/enable/{$params[2]}");
$app->disable("procedure/disable/{$params[2]}");
$app->delete("procedure/delete/{$params[2]}");

$app->getOverDueProcedures("procedure/overdue/");
$app->hide("procedure/notified/{$params[2]}");