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
            die(json_encode(ResponceHttp::status401("No tiene autorización para consumir esta API")));
        }

        $list = [''];

        if ($method === 'OPTIONS') {
            exit(0);
        }

        if (in_array($origin, $list)) {
            echo header("Access-Control-Allow-Origin: $origin",);
            echo header("Access-Control-Allow-Methods: GET, PUT, POST, PATH, DELETE");
            echo header("Allow: GET, PUT, POST, PATH, DELETE, OPTIONS");
            echo header("Access-Control-Allow-Headers: X-API-Key, Origin, X-Requested-With, Content-Type, Accept, Authorization");
            echo header("Content-Type: application/json");
        } else {
            die(json_encode(ResponceHttp::status401("No tiene autorización para consumir esta API")));
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

    public static function status200($status = true, $res = 'Operacion exitosa')
    {
        http_response_code(200);
        self::$response['status'] = $status;
        self::$response['message'] = $res;

        return self::$response;
    }

    public static function status201(string $res = 'Resurso creado')
    {
        http_response_code(201);
        self::$response['status'] = true;
        self::$response['message'] = $res;

        return self::$response;
    }

    public static function status400(string $res = 'Solicitud enviada incompleta o en formato incorrecto')
    {
        http_response_code(400);
        self::$response['status'] = false;
        self::$response['message'] = $res;

        return self::$response;
    }

    public static function status401(string $res = 'No tiene priviliegios para acceder al recurso solicitado')
    {
        http_response_code(401);
        self::$response['status'] = false;
        self::$response['message'] = $res;

        return self::$response;
    }

    public static function status404(string $res = 'Parece que estás perdido, revisa la documentación')
    {
        http_response_code(404);
        self::$response['status'] = false;
        self::$response['message'] = $res;

        return self::$response;
    }

    public static function status500(string $res = 'Error interno del servidor')
    {
        http_response_code(505);
        self::$response['status'] = false;
        self::$response['message'] = $res;

        return self::$response;
    }
}
