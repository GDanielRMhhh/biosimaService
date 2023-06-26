<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\db\ConectionDB;
use App\DB\Sql;
use App\Tools\Action;
use App\Tools\Module;
use App\Tools\Status;

class BranchModel extends ConectionDB
{
    private static int $idBranch;
    private static string $name;
    private static string $adress;
    private static int $idCompany;
    private static string $status;

    private static string $userSesion;

    public function __construct(array $data)
    {
        self::$idBranch = $data['idBranch'];
        self::$name = $data['name'];
        self::$adress = $data['adress'];
        self::$idCompany = $data['idCompany'];

        self::$userSesion = $data['userSesion'];
    }
    
    final public static function getIdBranch() { return self::$idBranch; }
    final public static function getName() { return self::$name; }
    final public static function getAdress() { return self::$adress; }
    final public static function getIdCompany() { return self::$idCompany; }
    final public static function getStatus() { return self::$status; }
    final public static function getUserSesion() { return self::$userSesion; }
    
    final public static function setIdBranch(int $idBranch) { self::$idBranch = $idBranch; }
    final public static function setName(string $name) { self::$name = $name; }
    final public static function setAdress(string $adress) { self::$adress = $adress; }
    final public static function setIdCompany(int $idCompany) { self::$idCompany = $idCompany; }
    final public static function setStatus(int $status) { self::$status = $status; }
    final public static function setUserSesion(string $userSesion) { self::$userSesion = $userSesion; }

    final public static function getAll()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("SELECT * FROM branch WHERE status <> :status ORDER BY name");
            $query->execute([
                ':status' => Status::DELETED,
            ]);
            $rs = $query->fetchAll(\PDO::FETCH_ASSOC);
            return ResponceHttp::status(ResponceHttp::STATUS_200,true,"Operacion exitosa", $rs);
        } catch (\PDOException $e) {
            error_log('BranchModel::getAll -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false, "No se pueden obtener los datos")));
        }
    }

    final public static function post()
    {
        try {
            if (self::getIdBranch() == 0) {
                return self::create();
            } else {
                if (Sql::exists("SELECT * FROM branch WHERE idBranch = :idBranch", ":idBranch", self::getIdBranch())) {
                    return self::update();
                } else {
                    return ResponceHttp::status(ResponceHttp::STATUS_400,false,"Este idBranch no existe; No se puede actulizar");
                }
            }
        } catch (\PDOException $e) {
            error_log('BranchModel::post -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false, "No se pueden obtener los datos")));
        }
    }

    final static function create()
    {
        $con = self::getConnection();
        $query1 = "INSERT INTO branch (name, adress, idCompany) VALUES";
        $query2 = "(:name, :adress, :idCompany)";

        $query = $con->prepare($query1 . $query2);
        $query->execute([
            ':name' => self::getName(),
            ':adress' => self::getAdress(),
            ':idCompany' => self::getIdCompany()
        ]);

        if ($query->rowCount() > 0) {
            $idBranchCreated = $con->lastInsertId();

            LogModel::newLog(self::getUserSesion(), Module::COMPANY, Action::CREATE, "Se creó la sucursal con ID: " . $idBranchCreated);
            return ResponceHttp::status(ResponceHttp::STATUS_200,true,"Sucursal creado correctamente");
        } else {
            return ResponceHttp::status(ResponceHttp::STATUS_500,false,'No se pudo crear la sucursal');
        }
    }

    final static function update()
    {
        $con = self::getConnection();
        $query = "UPDATE branch 
                SET 
                    name=:name, 
                    adress=:adress 
                WHERE 
                    idBranch=:idBranch";

        $query = $con->prepare($query);
        $query->execute([
            ':name' => self::getName(),
            ':adress' => self::getAdress(),
            ':idBranch' => self::getIdBranch()
        ]);

        LogModel::newLog(self::getUserSesion(), Module::COMPANY, Action::UPDATE, "Se modificó la sucursal con el ID: " . self::getIdBranch());
        return ResponceHttp::status(ResponceHttp::STATUS_200,true,'Sucursal modificada correctamente');
    }

    /**
     * Este procedimiento, a pesar de llamarse "delete", no elimina el registro sino que hace un borrado logico
     */
    final public static function delete()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("UPDATE branch SET status = :status WHERE idBranch = :idBranch");
            $query->execute([
                ':idBranch' => self::getIdBranch(),
                ':status' => Status::DELETED
            ]);
     
            LogModel::newLog(self::getUserSesion(), Module::COMPANY, Action::DELETE, 'Se eliminó la sucursal con ID: ' . self::getIdBranch() . ', Nombre: ' . self::getName() . ', ID Cliente: ' . self::getIdCompany());
        } catch (\PDOException $e) {
            error_log('BranchModel::delete -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false,"No se pudo eliminar la sucursal")));
        }
    }
}