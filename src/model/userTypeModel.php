<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\db\ConectionDB;
use App\DB\Sql;

class UserTypeModel extends ConectionDB
{
    private static int $idUserType;
    private static string $name;

    public function __construct(array $data)
    {
        self::$idUserType = $data['idUserType'];
        self::$name = $data['name'];
    }
    
    final public static function getIdUserType() { return self::$idUserType; }
    final public static function getName() { return self::$name; }
    
    final public static function setIdUserType(string $idUserType) { self::$idUserType = $idUserType; }
    final public static function setName(string $name) { self::$name = $name; }
    
    final public static function getAll()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("SELECT * FROM userType ORDER BY name");
            $query->execute();
            $rs = $query->fetchAll(\PDO::FETCH_ASSOC);
            
            return ResponceHttp::status(ResponceHttp::STATUS_200, true, "Tipos de usuario obtenidos correctamente", $rs);
        } catch (\PDOException $e) {
            error_log('UserTypeModel::getAll -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false,"No se pueden obtener los datos")));
        }
    }
}