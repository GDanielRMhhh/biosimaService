<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\db\ConectionDB;
use App\DB\Sql;

class CountryModel extends ConectionDB
{
    private static int $idCountry;
    private static string $name;
    private static string $code;

    public function __construct(array $data)
    {
        self::$idCountry = $data['idCountry'];
        self::$name = $data['name'];
        self::$code = $data['code'];
    }
    
    final public static function getIdCountry() { return self::$idCountry; }
    final public static function getName() { return self::$name; }
    final public static function getCode() { return self::$code; }
    
    final public static function setIdCountry(string $idCountry) { self::$idCountry = $idCountry; }
    final public static function setName(string $name) { self::$name = $name; }
    final public static function setCode(string $code) { self::$code = $code; }
    
    final public static function getAll()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("SELECT * FROM catcountries ORDER BY name");
            $query->execute();
            $rs = $query->fetchAll(\PDO::FETCH_ASSOC);

            return ResponceHttp::status(ResponceHttp::STATUS_200, true, "Paises obtenidos correctamente", $rs);
        } catch (\PDOException $e) {
            error_log('CountryModel::getAll -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false,"No se pueden obtener los datos")));
        }
    }
}