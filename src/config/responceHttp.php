<?php

namespace App\Config;

class ResponceHttp
{
    const STATUS_200 = 200;
    const STATUS_201 = 201;
    const STATUS_400 = 400;
    const STATUS_401 = 401;
    const STATUS_404 = 404;
    const STATUS_500 = 500;

    public static $response = array(
        'status' => '',
        'data' => '',
        'message' => ''
    );

    final public static function headerHttpPro($method, $origin)
    {
        if (!isset($origin)) {
            die(json_encode(ResponceHttp::status(self::STATUS_401, false, "No tiene autorización para consumir esta API")));
        }

        $list = ['https://wstest.biosima.org'];
        
        if (in_array($origin, $list)) {
            if ($method === 'OPTIONS') {
                echo header("Access-Control-Allow-Origin: $origin");
                echo header("Access-Control-Allow-Methods: GET, PUT, POST, PATH, DELETE");
                echo header("Access-Control-Allow-Headers: X-API-Key, Origin, X-Requested-With, Content-Type, Accept, Authorization");
                exit(0);
            } else {
                echo header("Access-Control-Allow-Origin: $origin");
                echo header("Access-Control-Allow-Methods: GET, PUT, POST, PATH, DELETE");
                echo header("Allow: GET, PUT, POST, PATH, DELETE, OPTIONS");
                echo header("Access-Control-Allow-Headers: X-API-Key, Origin, X-Requested-With, Content-Type, Accept, Authorization");
                echo header("Content-Type: application/json");
            }
        } else {
            die(json_encode(ResponceHttp::status(self::STATUS_401, false, "No tiene autorización para consumir esta API")));
        }
    }

    final public static function headerHttpDev($method)
    {
        if ($method === 'OPTIONS') {
            exit(0);
        }

        echo header("Access-Control-Allow-Origin: *",);
        echo header("Access-Control-Allow-Methods: GET, PUT, POST, PATH, DELETE");
        echo header("Allow: GET, PUT, POST, PATH, DELETE, OPTIONS");
        echo header("Access-Control-Allow-Headers: X-API-Key, Origin, X-Requested-With, Content-Type, Accept, Authorization");
        echo header("Content-Type: application/json");
    }

    public static function status($statusCode, $status = true, $message = 'Operacion exitosa', $data = [])
    {
        http_response_code($statusCode);
        self::$response['status'] = $status;
        self::$response['data'] = $data;
        self::$response['message'] = $message;

        return self::$response;
    }
}
