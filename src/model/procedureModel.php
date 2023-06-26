<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\db\ConectionDB;
use App\DB\Sql;
use App\Tools\Action;
use App\Tools\Module;
use App\Tools\Status;

class ProcedureModel extends ConectionDB
{
    private static int $idProcedure;
    private static string $name;
    private static string $procedureCode;
    private static int $idCompany;
    private static int $idBranch;
    private static bool $expires;
    private static string $description;
    private static string $dueDate;
    private static int $status;
    private static string $created;
    private static int $createdBy;
    private static int $notified;

    private static string $userSesion;

    public function __construct(array $data)
    {
        self::$idProcedure = $data['idProcedure'];
        self::$name = $data['name'];
        self::$procedureCode = $data['procedureCode'];
        self::$idCompany = $data['idCompany'];
        self::$idBranch = $data['idSubsidiary'];
        self::$expires = $data['expires'];
        self::$description = $data['description'];
        self::$dueDate = $data['dueDate'];
        self::$createdBy = $data['createdBy'];

        self::$userSesion = $data['userSesion'];
    }

    final public static function getIdProcedure() { return self::$idProcedure;}
    final public static function getName() { return self::$name;}
    final public static function getProcedureCode() { return self::$procedureCode;}
    final public static function getIdCompany() { return self::$idCompany;}
    final public static function getIdBranch() { return self::$idBranch;}
    final public static function getExpire() { return self::$expires;}
    final public static function getDescription() { return self::$description;}
    final public static function getDueDate() { return self::$dueDate;}
    final public static function getStatus() { return self::$status;}
    final public static function getCreated() { return self::$created;}
    final public static function getCreatedBy() { return self::$createdBy;}
    final public static function getNotified() { return self::$notified;}
    final public static function getUserSesion() { return self::$userSesion; }

    final public static function setIdProcedure(int $idProcedure) {self::$idProcedure = $idProcedure;}
    final public static function setName(string $name) {self::$name = $name;}
    final public static function setProcedureCode(string $procedureCode) {self::$procedureCode = $procedureCode;}
    final public static function setIdCompany(int $idCompany) {self::$idCompany = $idCompany;}
    final public static function setIdBranch(int $idBranch) {self::$idBranch = $idBranch;}
    final public static function setExpire(bool $expires) {self::$expires = $expires;}
    final public static function setDescription(string $description) {self::$description = $description;}
    final public static function setDueDate(string $dueDate) {self::$dueDate = $dueDate;}
    final public static function setStatus(int $status) {self::$status = $status;}
    final public static function setCreated(string $created) {self::$created = $created;}
    final public static function setCreatedBy(string $createdBy) {self::$createdBy = $createdBy;}
    final public static function setNotified(string $notified) {self::$notified = $notified;}
    final public static function setUserSesion(string $userSesion) { self::$userSesion = $userSesion; }

    final public static function getAll()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("SELECT
                                        P.idProcedure,
                                        P.name,
                                        P.procedureCode,
                                        C.name AS company,
                                        B.name AS branch,
                                        P.created,
                                        P.status AS idStatus,
                                        S.name AS statusName
                                    FROM 
                                        procedures AS P
                                        INNER JOIN company AS C ON C.idCompany = P.idCompany
                                        INNER JOIN branch AS B ON B.idBranch = P.idBranch
                                        INNER JOIN status AS S ON S.idStatus = P.status
                                    WHERE 
                                        P.status <> :status
                                    ORDER BY 
                                        P.idProcedure DESC"
            );
            $query->execute([
                'status' => Status::DELETED
            ]);
            $rs['data'] = $query->fetchAll(\PDO::FETCH_ASSOC);
            return $rs;
        } catch (\PDOException $e) {
            error_log('ProcedureModel::getAll -> ' . $e);
            die(json_encode(ResponceHttp::status500("No se pueden obtener los datos")));
        }
    }

    final public static function getById()
    {
        try {
            $conProcedure = self::getConnection();
            $queryProcedure = $conProcedure->prepare(
                "SELECT
                                        idProcedure,
                                        name,
                                        procedureCode,
                                        idCompany,
                                        idBranch,
                                        expire,
                                        CAST(dueDate AS DATE) AS dueDate,
                                        description
                                    FROM 
                                        procedures
                                    WHERE idProcedure = :idProcedure"
            );
            $queryProcedure->execute([
                'idProcedure' => self::getIdProcedure(),
            ]);

            if ($queryProcedure->rowCount() === 0) {
                return ResponceHttp::status400("No existe un tramite con este idProcedure");
            } else {
                $conContact = self::getConnection();
                $queryContact = $conContact->prepare("SELECT
                                                        C.idContact,
                                                        C.name,
                                                        C.email,
                                                        C.idCountry,
                                                        CC.code AS countryCode,
                                                        C.phone,
                                                        C.contactType,
                                                        C.idProcedure,
                                                        C.createdBy
                                                    FROM 
                                                        contact AS C
                                                        INNER JOIN catcountries AS CC ON CC.idCountry = C.idCountry
                                                    WHERE C.idProcedure = :idProcedure "
                );
                $queryContact->execute([
                    'idProcedure' => self::getIdProcedure(),
                ]);

                $rsProcedure['data'] = $queryProcedure->fetch(\PDO::FETCH_ASSOC);
                $rsContact['data'] = $queryContact->fetchAll(\PDO::FETCH_ASSOC);
                
                $data = array(
                    'procedure' => $rsProcedure['data'],
                    'contact' => $rsContact['data']
                );

                return $data;
            }
        } catch (\PDOException $e) {
            error_log('ProcedureModel::getById -> ' . $e);
            die(json_encode(ResponceHttp::status500("No se pueden obtener los datos")));
        }
    }

    final public static function post()
    {
        try {
            if (self::getIdProcedure() == 0) {
                return self::create();
            } else {
                if (Sql::exists("SELECT * FROM procedures WHERE idProcedure = :idProcedure", ":idProcedure", self::getIdProcedure())) {
                    return self::update();
                } else {
                    return ResponceHttp::status(ResponceHttp::STATUS_400,false,"Este idProcedure no existe; No se puede actulizar");
                }
            }
        } catch (\PDOException $e) {
            error_log('ProcedureModel::post -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false,"No se pudo completar la operacion")));
        }
    }

    final static function create()
    {
        self::setCreated(date('Y-m-d H:i:s'));

        $con = self::getConnection();
        $query1 = "INSERT INTO procedures (name, procedureCode, idCompany, idBranch, expire, description, dueDate, created, createdBy) VALUES";
        $query2 = "(:name, :procedureCode, :idCompany, :idBranch, :expire, :description, :dueDate, :created, :createdBy)";

        $query = $con->prepare($query1 . $query2);
        $query->execute([
            ':name' => self::getName(),
            ':procedureCode' => self::getProcedureCode(),
            ':idCompany' => self::getIdCompany(),
            ':idBranch' => self::getIdBranch(),
            ':expire' => self::getExpire(),
            ':description' => self::getDescription(),
            ':dueDate' => self::getDueDate(),
            ':created' => self::getCreated(),
            ':createdBy' => self::getCreatedBy()
        ]);

        if ($query->rowCount() > 0) {
            $idProcedireCreated = $con->lastInsertId(); 

            LogModel::newLog(self::getUserSesion(), Module::PROCEDURE, Action::CREATE, 'Se creó el tramite con ID: ' . $idProcedireCreated);
            return [
                'idProcedure' => $idProcedireCreated,
                'responce' => ResponceHttp::status(ResponceHttp::STATUS_200,true,"Tramite creado correctamente")
            ];
        } else {
            return ResponceHttp::status(ResponceHttp::STATUS_500,true,'No se pudo crear el tramite');
        }
    }

    final static function update()
    {
        $con = self::getConnection();
        $query = "UPDATE procedures 
                SET 
                    name=:name, 
                    procedureCode=:procedureCode, 
                    idCompany=:idCompany, 
                    idBranch=:idBranch, 
                    expire=:expire, 
                    description=:description, 
                    dueDate=:dueDate
                WHERE 
                    idProcedure=:idProcedure";

        $query = $con->prepare($query);
        $query->execute([
            ':idProcedure' => self::getIdProcedure(),
            ':name' => self::getName(),
            ':procedureCode' => self::getProcedureCode(),
            ':idCompany' => self::getIdCompany(),
            ':idBranch' => self::getIdBranch(),
            ':expire' => self::getExpire(),
            ':description' => self::getDescription(),
            ':dueDate' => self::getDueDate()
        ]);

        LogModel::newLog(self::getUserSesion(), Module::PROCEDURE, Action::UPDATE, 'Se modificó el tramite con ID: ' . self::getIdProcedure());
        return [
            'idProcedure' => self::getIdProcedure(),
            'responce' => ResponceHttp::status(ResponceHttp::STATUS_200,true,'Tramite modificado correctamente')
        ];
    }

    final public static function updateStatus()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("UPDATE procedures SET status = :status WHERE idProcedure = :idProcedure");
            $query->execute([
                ':status' => self::getStatus(),
                ':idProcedure' => self::getIdProcedure()
            ]);

            LogModel::newLog(self::getUserSesion(), Module::PROCEDURE, Action::getActionSring(self::getStatus()), 'Se modificó el status del tramite con ID: ' . self::getIdProcedure());
        } catch (\PDOException $e) {
            error_log('ProcedureModel::updateStatus -> ' . $e);
            die(json_encode(ResponceHttp::status500()));
        }
    }

    final public static function overDueProcedures(){
        try {
            $setting = SettingModel::getSetting();

            $con = self::getConnection();
            $query = $con->prepare("SELECT
                                        P.idProcedure,
                                        P.name,
                                        P.procedureCode,
                                        P.idCompany,
                                        P.idCompany,
                                        C.name AS companyName,
                                        P.idBranch,
                                        B.name AS branchName,
                                        CAST(P.dueDate AS DATE) AS dueDate
                                    FROM
                                        procedures AS P
                                        INNER JOIN company AS C ON C.idCompany = P.idCompany
                                        INNER JOIN branch AS B ON B.idBranch = P.idBranch
                                    WHERE 
                                        P.status = 1
                                        AND P.expire = 1
                                        AND P.notified = 0
                                        AND (
                                                TIMESTAMPDIFF(DAY,NOW(),P.dueDate) BETWEEN 0 AND :marginDays
                                        )
                                    ORDER BY 
                                        P.dueDate");
            $query->execute([
                'marginDays' => $setting['data']['marginDays']
            ]);
            $rs['data'] = $query->fetchAll(\PDO::FETCH_ASSOC);
            return $rs;
        } catch (\PDOException $e) {
            error_log('UserModel::getUser -> ' . $e);
            die(json_encode(ResponceHttp::status500("No se pueden obtener los datos")));
        }
    }

    final public static function hide()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("UPDATE procedures SET notified = :notified WHERE idProcedure = :idProcedure");
            $query->execute([
                ':idProcedure' => self::getIdProcedure(),
                ':notified' => self::getNotified()
            ]);

            LogModel::newLog(self::getUserSesion(), Module::PROCEDURE, Action::UNSHOW, 'Se ocultó el tramite con ID: ' . self::getIdProcedure());
        } catch (\PDOException $e) {
            error_log('ProcedureModel::updateStatus -> ' . $e);
            die(json_encode(ResponceHttp::status500()));
        }
    }
}
