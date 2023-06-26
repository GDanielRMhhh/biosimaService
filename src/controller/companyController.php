<?php

namespace App\Controller;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\Model\BranchModel;
use App\Model\CompanyModel;
use App\Tools\HttpMethod;
use App\Tools\Status;

class CompanyController
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

    final public function getAll(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            echo json_encode(CompanyModel::getAll());
            exit;
        }
    }

    final public function getById(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            $idCompany = $this->params[1];
            if (!isset($idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Es requerido el idCompany"));
            } else if (!preg_match(self::$validateNumer, $idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"El idCompany debe ser númerico"));
            } else {
                CompanyModel::setIdCompany($idCompany);
                echo json_encode(CompanyModel::getById());
                exit;
            }
        }
    }

    final public function post(string $endpoint)
    {
        if ($this->method == HttpMethod::POST && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());

            $validCompany = false;
            $validBranch = false;

            if (is_null($this->data['company']['idCompany']) || empty($this->data['company']['companyName'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Todos los campos son requeridos"));
            } else if (!preg_match(self::$validateNumer, $this->data['company']['idCompany'])) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"El idCompany solo admite números"));
            } else if (!preg_match(self::$validateText, str_replace(' ', 'STR', $this->data['company']['companyName']))) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"El companyName solo admite texto"));
            } else {
                $validCompany = true;
            }

            if (count($this->data['branch']) <= 0) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Por favor, indique al menos una sucursal para guardar a este cliente"));
            } else {
                $validBranch = true;
            }

            $resp = array(
                "company" => [],
                "branch" => []
            );
            if ($validCompany && $validBranch) {
                new CompanyModel($this->data['company']);
                $resp["company"] = CompanyModel::post();

                foreach ($this->data['branch'] as $key => $branch) {
                    $branch['userSesion'] = $this->data['company']['userSesion'];
                    $branch['idCompany'] = $resp["company"]["idCompany"];
                    new BranchModel($branch);

                    if ($branch['delete']) {
                        array_push($resp['branch'],BranchModel::delete());
                        $resp['branch'][$key]['branchName'] = $branch['name'];
                    } else {
                        array_push($resp['branch'],BranchModel::post());
                        $resp['branch'][$key]['branchName'] = $branch['name'];
                    }
                }

                echo json_encode($resp);
            }
        }
    }

    final public function enable(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idCompany = $this->params[2];
            if (!isset($idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Es requerido el idCompany"));
            } else if (!is_numeric($idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"El idCompany debe ser númerico"));
            } else {
                CompanyModel::setIdCompany($idCompany);
                CompanyModel::setStatus(Status::ENABLED);
                CompanyModel::setUserSesion($this->data['userName']);
                echo json_encode(CompanyModel::updateStatus());
                exit;
            }
        }
    }

    final public function disable(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idCompany = $this->params[2];
            if (!isset($idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Es requerido el idCompany"));
            } else if (!is_numeric($idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"El idCompany debe ser númerico"));
            } else {
                CompanyModel::setIdCompany($idCompany);
                CompanyModel::setStatus(Status::DISABLED);
                CompanyModel::setUserSesion($this->data['userName']);
                echo json_encode(CompanyModel::updateStatus());
                exit;
            }
        }
    }

    final public function delete(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idCompany = $this->params[2];
            if (!isset($idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"Es requerido el idCompany"));
            } else if (!is_numeric($idCompany)) {
                echo json_encode(ResponceHttp::status(ResponceHttp::STATUS_400,false,"El idCompany debe ser númerico"));
            } else {
                CompanyModel::setIdCompany($idCompany);
                CompanyModel::setStatus(Status::DELETED);
                CompanyModel::setUserSesion($this->data['userName']);
                echo json_encode(CompanyModel::updateStatus());
                exit;
            }
        }
    }
}
