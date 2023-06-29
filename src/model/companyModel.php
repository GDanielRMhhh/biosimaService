<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\Db\ConectionDB;
use App\Db\Sql;
use App\Tools\Action;
use App\Tools\Module;
use App\Tools\Status;

class CompanyModel extends ConectionDB
{
    private static int $idCompany;
    private static string $name;
    private static string $created;
    private static int $status;

    private static string $userSesion;

    public function __construct(array $data)
    {
        self::$idCompany = $data['idCompany'];
        self::$name = $data['companyName'];
        // self::$status = $data['status'];
        // self::$created = $data['created'];

        self::$userSesion = $data['userSesion'];
    }
    
    final public static function getIdCompany() { return self::$idCompany; }
    final public static function getName() { return self::$name; }
    final public static function getStatus() { return self::$status; }
    final public static function getCreated() { return self::$created; }
    final public static function getUserSesion() { return self::$userSesion; }
    
    final public static function setIdCompany(int $idCompany) { self::$idCompany = $idCompany; }
    final public static function setName(string $name) { self::$name = $name; }
    final public static function setStatus(int $status) { self::$status = $status; }
    final public static function setCreated(string $created) { self::$created = $created; }
    final public static function setUserSesion(string $userSesion) { self::$userSesion = $userSesion; }

    final public static function getAll()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare(
                "SELECT
                    C.idCompany,
                    C.name AS companyName,
                    C.created,
                    C.status AS idStatus,
                    S.name AS statusName
                FROM 
                    Company AS C
                    INNER JOIN status AS S ON S.idStatus = C.status 
                WHERE C.status <> :status
                ORDER BY C.idCompany"
            );
            $query->execute([
                ':status' => Status::DELETED
            ]);
            $rs = $query->fetchAll(\PDO::FETCH_ASSOC);
            return ResponceHttp::status(ResponceHttp::STATUS_200, true, "Datos obtenidos correctamente", $rs);
        } catch (\PDOException $e) {
            error_log('CompanyModel::getAll -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, "No se pueden obtener los datos")));
        }
    }

    final public static function getById()
    {
        try {
            $conCompany = self::getConnection();
            $queryCompany = $conCompany->prepare("SELECT
                                                idCompany,
                                                name AS companyName
                                            FROM company
                                            WHERE idCompany = :idCompany");
            $queryCompany->execute([
                'idCompany' => self::getIdCompany(),
            ]);

            if ($queryCompany->rowCount() === 0) {
                return ResponceHttp::status(ResponceHttp::STATUS_400, false, "No existe un cliente con este idCompany");
            } else {
                $conBranch = self::getConnection();
                $queryBranch = $conBranch->prepare("SELECT
                                                    idBranch,
                                                    name,
                                                    adress,
                                                    idCompany
                                                FROM branch 
                                                WHERE idCompany = :idCompany
                                                AND status <> :status
                                                ORDER BY name");
                $queryBranch->execute([
                    'idCompany' => self::getIdCompany(),
                    'status' => Status::DELETED
                ]);

                $rsBranch['data'] = $queryBranch->fetchAll(\PDO::FETCH_ASSOC);
                $rsCompany['data'] = $queryCompany->fetch(\PDO::FETCH_ASSOC);

                $data = array(
                    'company' => $rsCompany['data'],
                    'branch' => $rsBranch['data']
                );

                return ResponceHttp::status(ResponceHttp::STATUS_200, true, "Datos obtenidos correctamente", $data);
            }
        } catch (\PDOException $e) {
            error_log('CompanyModel::getById -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, "No se pueden obtener los datos")));
        }
    }

    final public static function post()
    {
        try {
            if (self::getIdCompany() == 0) {
                return self::create();
            } else {
                if (Sql::exists("SELECT * FROM company WHERE idCompany = :idCompany", ":idCompany", self::getIdCompany())) {
                    return self::update();
                } else {
                    return ResponceHttp::status(ResponceHttp::STATUS_400, false, "Este idCompany no existe; No se puede actulizar");
                }
            }
        } catch (\PDOException $e) {
            error_log('CompanyModel::post -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "No se pudo completar la operación")));
        }
    }

    final static function create()
    {
        self::setCreated(date('Y-m-d H:i:s'));

        $con = self::getConnection();
        $query1 = "INSERT INTO company (name, created, status) VALUES";
        $query2 = "(:name, :created, :status)";

        $query = $con->prepare($query1 . $query2);
        $query->execute([
            ':name' => self::getName(),
            ':created' => self::getCreated(),
            ':status' => 1
        ]);

        if ($query->rowCount() > 0) {
            $idCompanyCreated = $con->lastInsertId();

            LogModel::newLog(self::getUserSesion(), Module::COMPANY, Action::CREATE, "Se creó el cliente con ID:" . $idCompanyCreated);
            return [
                'idCompany' => $con->lastInsertId(),
                'responce' => ResponceHttp::status(ResponceHttp::STATUS_200, true, "Cliente creado correctamente")
            ];
        } else {
            return ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo crear el cliente');
        }
    }

    final static function update()
    {
        $con = self::getConnection();
        $query = "UPDATE company SET name=:name WHERE idCompany=:idCompany";

        $query = $con->prepare($query);
        $query->execute([
            ':idCompany' => self::getIdCompany(),
            ':name' => self::getName()
        ]);

        LogModel::newLog(self::getUserSesion(), Module::COMPANY, Action::UPDATE, "Se modificó el cliente con ID:" . self::getIdCompany());
        return [
            'idCompany' => self::getIdCompany(),
            'responce' => ResponceHttp::status(ResponceHttp::STATUS_200)
        ];
    }

    final public static function updateStatus()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("UPDATE company SET status = :status WHERE idCompany = :idCompany");
            $query->execute([
                ':status' => self::getStatus(),
                ':idCompany' => self::getIdCompany()
            ]);

            LogModel::newLog(self::getUserSesion(), Module::COMPANY, Action::getActionSring(self::getStatus()), "Se modificó el status del cliente con ID:" . self::getIdCompany());
            return ResponceHttp::status(ResponceHttp::STATUS_200, true);
        } catch (\PDOException $e) {
            error_log('CompanyModel::updateStatus -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_400, false, "No se pudo modificar el status del cliente")));
        }
    }
}