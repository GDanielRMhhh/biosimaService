<?php

namespace App\Controller;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\Model\SettingModel;
use App\Tools\HttpMethod;

class SettingController
{
    private static $validateNumer = '/^[0-9]+$/';
    private static $validateTime = '/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/';

    public function __construct(
        private string $method,
        private string $route,
        private array $params,
        private $data,
        private $headers,
    ) {
    }

    final public function getSetting(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            echo json_encode(SettingModel::getSetting());
            exit;
        }
    }

    final public function post(string $endpoint)
    {
        if ($this->method == HttpMethod::POST && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());

            if (
                empty($this->data['notificationTime']) ||
                empty($this->data['marginDays']) ||
                empty($this->data['msnClient']) ||
                empty($this->data['msnBiosima']) ||
                empty($this->data['notificationContacts'])
            ) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "Todos los campos son requeridos"));
            } else if (!preg_match(self::$validateTime, $this->data['notificationTime'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El notificationTime solo admite texto"));
            } else if (!preg_match(self::$validateNumer, $this->data['marginDays'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "El marginDays solo admite números"));
            } else {
                new SettingModel($this->data);
                echo json_encode(SettingModel::post());
            }
        }
    }
}
