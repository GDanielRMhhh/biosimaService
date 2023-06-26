<?php

namespace App\Controller;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\Model\ContactModel;
use App\Model\ProcedureModel;
use App\Tools\HttpMethod;
use App\Tools\Status;

class ProcedureController
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
            echo json_encode(ProcedureModel::getAll());
            exit;
        }
    }

    final public function getOverDueProcedures(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            echo json_encode(ProcedureModel::overDueProcedures());
            exit;
        }
    }

    final public function hide(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idProcedure = $this->params[2];
            if (!isset($idProcedure)) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "Es requerido el idProcedure");
            } else if (!is_numeric($idProcedure)) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El idProcedure debe ser númerico");
            } else {
                ProcedureModel::setIdProcedure($idProcedure);
                ProcedureModel::setNotified(1);
                ProcedureModel::setUserSesion($this->data['userSesion']);
                echo json_encode(ProcedureModel::hide());
                exit;
            }
        }
    }

    final public function getById(string $endpoint)
    {
        if ($this->method == HttpMethod::GET && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());
            $idProcedure = $this->params[1];
            if (!isset($idProcedure)) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "Es requerido el idProcedure");
            } else if (!preg_match(self::$validateNumer, $idProcedure)) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El idProcedure debe ser númerico");
            } else {
                ProcedureModel::setIdProcedure($idProcedure);
                echo json_encode(ProcedureModel::getById());
                exit;
            }
        }
    }

    final public function post(string $endpoint)
    {
        if ($this->method == HttpMethod::POST && $endpoint == $this->route) {
            // Security::validateTokenJwt($this->headers, Security::secretKey());

            $validProcedure = false;
            $validContact = false;

            if (
                is_null($this->data['procedure']['idProcedure']) ||
                empty($this->data['procedure']['name']) ||
                empty($this->data['procedure']['idCompany']) ||
                empty($this->data['procedure']['idSubsidiary']) ||
                empty($this->data['procedure']['procedureCode']) ||
                empty($this->data['procedure']['dueDate']) ||
                empty($this->data['procedure']['createdBy']) ||
                empty($this->data['procedure']['description']) ||
                empty($this->data['procedure']['expires'])
            ) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "Todos los campos son requeridos");
            } else if (!preg_match(self::$validateNumer, $this->data['procedure']['idProcedure'])) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El idProcedure solo admite números");
            } else if (!preg_match(self::$validateNumer, $this->data['procedure']['idCompany'])) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El idCompany solo admite números");
            } else if (!preg_match(self::$validateNumer, $this->data['procedure']['idSubsidiary'])) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El idSubsidiary solo admite números");
            } else if (!preg_match(self::$validateNumer, $this->data['procedure']['createdBy'])) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El createdBy solo admite números");
            } else if (!preg_match(self::$validateText, str_replace(' ', 'STR', $this->data['procedure']['name']))) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El name solo admite texto");
            } else if (strlen($this->data['procedure']['description']) > 300) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El description solo admite 300 caracteres");
            } else if (!preg_match(self::$validateText, str_replace(' ', 'STR', $this->data['procedure']['name']))) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "El name solo admite texto");
            } else {
                $validProcedure = true;
            }

            if (count($this->data['contacts']) <= 0) {
                echo ResponceHttp::status(ResponceHttp::STATUS_200, false, "Por favor, indique al menos un contacto para guardar a este tramite");
            } else {
                $validContact = true;
            }

            $resp = array(
                "procedure" => [],
                "contacts" => []
            );

            if ($validProcedure && $validContact) {
                new ProcedureModel($this->data['procedure']);
                $resp['procedure'] = ProcedureModel::post();
                
                foreach ($this->data['contacts'] as $key => $contact) {
                    $contact['userSesion'] = $this->data['procedure']['userSesion'];
                    $contact['idProcedure'] = $resp['procedure']['idProcedure'];
                    new ContactModel($contact);

                    if ($contact['delete']) {
                        array_push($resp['contacts'], ContactModel::delete());
                        $resp['contacts'][$key]['name'] = $contact['name'];
                    } else {
                        array_push($resp['contacts'], ContactModel::post());
                        $resp['contacts'][$key]['name'] = $contact['name'];
                    }
                }

                echo json_encode($resp);
            }
        }
    }

    final public function enable(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idProcedure = $this->params[2];
            if (!isset($idProcedure)) {
                echo json_encode(ResponceHttp::status200("Es requerido el idProcedure"));
            } else if (!is_numeric($idProcedure)) {
                echo json_encode(ResponceHttp::status200("El idProcedure debe ser númerico"));
            } else {
                ProcedureModel::setIdProcedure($idProcedure);
                ProcedureModel::setStatus(Status::ENABLED);
                ProcedureModel::setUserSesion($this->data['userSesion']);
                echo json_encode(ProcedureModel::updateStatus());
                exit;
            }
        }
    }

    final public function disable(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idProcedure = $this->params[2];
            if (!isset($idProcedure)) {
                echo json_encode(ResponceHttp::status200("Es requerido el idProcedure"));
            } else if (!is_numeric($idProcedure)) {
                echo json_encode(ResponceHttp::status200("El idProcedure debe ser númerico"));
            } else {
                ProcedureModel::setIdProcedure($idProcedure);
                ProcedureModel::setStatus(Status::DISABLED);
                ProcedureModel::setUserSesion($this->data['userSesion']);
                echo json_encode(ProcedureModel::updateStatus());
                exit;
            }
        }
    }

    final public function delete(string $endpoint)
    {
        if ($this->method == HttpMethod::PUT && $endpoint == $this->route) {
            $idProcedure = $this->params[2];
            if (!isset($idProcedure)) {
                echo json_encode(ResponceHttp::status200("Es requerido el idProcedure"));
            } else if (!is_numeric($idProcedure)) {
                echo json_encode(ResponceHttp::status200("El idProcedure debe ser númerico"));
            } else {
                ProcedureModel::setIdProcedure($idProcedure);
                ProcedureModel::setStatus(Status::DELETED);
                ProcedureModel::setUserSesion($this->data['userSesion']);
                echo json_encode(ProcedureModel::updateStatus());
                exit;
            }
        }
    }
}
