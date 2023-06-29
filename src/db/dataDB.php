<?php

use App\Db\ConectionDB;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$data = array(
    "user" => $_ENV['USER'],
    "password" => $_ENV['PASSWORD'],
    "db" => $_ENV['DB'],
    "ip" => $_ENV['IP'],
    "port" => $_ENV['PORT']
);

$host = 'mysql:host=' . $data['ip'] . ';' . 'port=' . $data['port'] . ';' . 'dbname=' . $data['db'];

ConectionDB::from($host, $data['user'], $data['password']);
