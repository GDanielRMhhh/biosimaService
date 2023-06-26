<?php

namespace App\Config;

use DateTime;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;

class Security
{
    private static  $jwt_data;

    final public static function secretKey()
    {
        $dotEnv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotEnv->load();
        return $_ENV['SECRET_KEY'];
    }

    final public static function createPassword(string $pw)
    {
        $pass = password_hash($pw, PASSWORD_DEFAULT);
        return $pass;
    }

    final public static function validatePassword(string $pw, string $pwh)
    {
        // if (password_verify($pw, $pwh)) {
        if ($pw == $pwh) {
            return true;
        } else {
            error_log('La contraseña es incorrecta(' . $pw . ')');
            return false;
        }
    }

    final public static function createTokenJwt(string $key, array $data)
    {
        $payload = array(
            "iat" => time(),
            "exp" => time() + (60),
            "data" => $data
        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    final public static function validateTokenJwt(array $token, string $key)
    {
        if (!isset($token['Authorization'])) {
            die(json_encode(ResponceHttp::status400('El token de acceso es requerido')));
            exit;
        }

        try {
            $jwt = explode(" ", $token['Authorization']);
            $data = JWT::decode($jwt[1], $key,array('HS256'));
            self::$jwt_data = $data;
            return $data;
        } catch (\Exception $e) { 
            error_log($e);
            error_log('Token invalido o expirado');
            die(json_encode(ResponceHttp::status401('Token invalido o expirado')));
        }
    }

    final public static function getDataJwt()
    {
        $jwt_decoded_array = json_decode(json_encode(self::$jwt_data), TRUE);
        return $jwt_decoded_array['data'];
        exit;
    }
}
