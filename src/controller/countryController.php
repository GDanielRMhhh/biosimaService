<?php

namespace App\Controller;

use App\Config\Security;
use App\Model\CountryModel;
use App\Tools\HttpMethod;

class CountryController
{
    public function __construct(
        private string $method,
        private string $route,
        private array $params,
        private $data,
        private $headers,
    ) { }

    final public function getAll(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            echo json_encode(CountryModel::getAll());
            exit;
        }
    }
}
