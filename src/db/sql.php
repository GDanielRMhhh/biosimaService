<?php

namespace App\Db;

use App\Config\ResponceHttp;
use App\Db\ConectionDB;

class Sql extends ConectionDB
{
    public static function exists(string $request, string $condition, $param)
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare($request);
            $query->execute([
                $condition => $param
            ]);
            $res = ($query->rowCount() == 0) ? false : true;
            return $res;
        } catch (\PDOException $p) {
            error_log('sql::exists->' . $p);
            die(ResponceHttp::status(ResponceHttp::STATUS_500,false,''));
        }
    }
}
