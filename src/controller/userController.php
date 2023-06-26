<?php

namespace App\Controller;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\Model\UserModel;
use App\Tools\HttpMethod;
use App\Tools\Status;

class UserController
{
    private static $validateNumer = '/^[0-9]+$/';
    private static $validateText  = '/^[a-zA-Z]+$/';

    public function __construct(
        private string $method,
        private string $route,
        private array $params,
        private $data,
        private $headers,
    ) {
    }

    final public function getLogin(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            $userName = $this->params[1];
            $password = $this->params[2];

            if (empty($userName) || !isset($userName) || empty($password) || !isset($password)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Todos los campos son requeridos"));
            } else {
                UserModel::setUserName($userName);
                UserModel::setPassword($password);
                echo json_encode(UserModel::login());
            }
            exit;
        }
    }

    final public function getAll(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            echo json_encode(UserModel::getAll());
            exit;
        }
    }

    final public function getUser(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            $idUser = $this->params[1];
            if (!isset($idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Es requerido el idUser"));
            } else if (!preg_match(self::$validateNumer, $idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"El idUser debe ser númerico"));
            } else {
                UserModel::setIdUser($idUser);
                echo json_encode(UserModel::getUser());
                exit;
            }
        }
    }

    final public function post(string $endpoint)
    {
        if ($this->method == HttpMethod::POST && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());

            if (
                is_null($this->data['idUser']) ||
                empty($this->data['userName']) ||
                empty($this->data['password']) ||
                empty($this->data['userType']) ||

                empty($this->data['employeeName']) ||
                empty($this->data['email']) ||
                empty($this->data['idCountryCode']) ||
                empty($this->data['phone'])

            ) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "Todos los campos son requeridos"));
            } else if (!preg_match(self::$validateNumer, $this->data['idUser'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El idUser solo admite números"));
            } else if (!preg_match(self::$validateText, trim($this->data['userName']))) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El userName solo admite texto"));
            } else if (strlen($this->data['password']) < 8) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El password debe contener al menos 8 caracteres"));
            } else if (!preg_match(self::$validateNumer, $this->data['userType'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El idUserType solo admite números"));
            } else if (!preg_match(self::$validateText, str_replace(' ', 'STR', $this->data['employeeName']))) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El employeeName solo admite texto"));
            } else if (!filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El email solo admite texto"));
            } else if (!preg_match(self::$validateNumer, $this->data['idCountryCode'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El idCountryCode solo admite números"));
            } else if (!preg_match(self::$validateNumer, $this->data['phone'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El phone solo admite números"));
            } else if (strlen($this->data['phone']) < 10) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El phone debe contener al menos 10 caracteres"));
            } else {
                new UserModel($this->data);
                echo json_encode(UserModel::post());
            }
        }
    }

    final public function enable(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idUser = $this->params[2];
            if (!isset($idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "Es requerido el idUser"));
            } else if (!is_numeric($idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El idUser debe ser númerico"));
            } else {
                UserModel::setIdUser($idUser);
                UserModel::setStatus(Status::ENABLED);
                UserModel::setUserSesion($this->data['userSesion']);
                echo json_encode(UserModel::updateStatusUser());
                exit;
            }
        }
    }

    final public function disable(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idUser = $this->params[2];
            if (!isset($idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "Es requerido el idUser"));
            } else if (!is_numeric($idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El idUser debe ser númerico"));
            } else {
                UserModel::setIdUser($idUser);
                UserModel::setStatus(Status::DISABLED);
                UserModel::setUserSesion($this->data['userSesion']);
                echo json_encode(UserModel::updateStatusUser());
                exit;
            }
        }
    }

    final public function delete(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idUser = $this->params[2];
            if (!isset($idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "Es requerido el idUser"));
            } else if (!is_numeric($idUser)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El idUser debe ser númerico"));
            } else {
                UserModel::setIdUser($idUser);
                UserModel::setStatus(Status::DELETED);
                UserModel::setUserSesion($this->data['userSesion']);
                echo json_encode(UserModel::updateStatusUser());
                exit;
            }
        }
    }
}
