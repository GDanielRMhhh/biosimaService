<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\db\ConectionDB;
use App\DB\Sql;
use App\Tools\Action;
use App\Tools\Module;

class UserModel extends ConectionDB
{
    private static int $idUser;
    private static string $userName;
    private static string $password;
    private static string $employeeName;
    private static string $email;
    private static int $idCountryCode;
    private static string $phoneNumber;
    private static int $idUserType;
    private static int $status;
    private static string $created;
    private static string $token;

    private static string $userSesion;

    public function __construct(array $data)
    {
        error_log(json_encode($data));
        self::$idUser = $data['idUser'];
        self::$userName = $data['userName'];
        self::$password = $data['password'];
        self::$employeeName = $data['employeeName'];
        self::$email = $data['email'];
        self::$idCountryCode = $data['idCountryCode'];
        self::$phoneNumber = $data['phone'];
        self::$idUserType = $data['userType'];

        self::$userSesion = $data['userSesion'];
    }
    
    final public static function getIdUser() { return self::$idUser; }
    final public static function getUserName() { return self::$userName; }
    final public static function getPassword() { return self::$password; }
    final public static function getEmployeeName() { return self::$employeeName; }
    final public static function getEmail() { return self::$email; }
    final public static function getIdCountryCode() { return self::$idCountryCode; }
    final public static function getPhoneNumber() { return self::$phoneNumber; }
    final public static function getIdUserType() { return self::$idUserType; }
    final public static function getStatus() { return self::$status; }
    final public static function getCreated() { return self::$created; }
    final public static function getToken() { return self::$token; }
    final public static function getUserSesion() { return self::$userSesion; }
    
    final public static function setIdUser(string $idUser) { self::$idUser = $idUser; }
    final public static function setUserName(string $userName) { self::$userName = $userName; }
    final public static function setPassword(string $password) { self::$password = $password; }
    final public static function setEmployeeName(string $employeeName) { self::$employeeName = $employeeName; }
    final public static function setEmail(string $email) { self::$email = $email; }
    final public static function setIdCountryCode(string $idCountryCode) { self::$idCountryCode = $idCountryCode; }
    final public static function setPhoneNumber(string $phoneNumber) { self::$phoneNumber = $phoneNumber; }
    final public static function setIdUserType(int $idUserType) { self::$idUserType = $idUserType; }
    final public static function setStatus(string $status) { self::$status = $status; }
    final public static function setCreated(string $created) { self::$created = $created; }
    final public static function setToken(string $token) { self::$token = $token; }
    final public static function setUserSesion(string $userSesion) { self::$userSesion = $userSesion; }

    final public static function login()
    {
        try {
            $con = self::getConnection()->prepare("SELECT
                                                        U.idUser,
                                                        U.userName,
                                                        U.employeeName,
                                                        U.password,
                                                        UT.idUserType,
                                                        UT.name AS userTypeName,
                                                        U.token
                                                    FROM 
                                                        user AS U
                                                        INNER JOIN usertype AS UT ON UT.idUserType = U.userType
                                                    WHERE U.userName = :userName");
            $con->execute([
                ':userName' => self::getUserName()
            ]);

            if ($con->rowCount() === 0) {
                return ResponceHttp::status(ResponceHttp::STATUS_404, false, "Usuario incorrecto");
            } else {
                foreach ($con as $res) {
                    if (Security::validatePassword(self::getPassword(), $res['password'])) {
                        $payload = ['token' => $res['token']];
                        $token = Security::createTokenJwt(Security::secretKey(), $payload);

                        $data = [
                            'idUser' => $res['idUser'],
                            'userName' => $res['userName'],
                            'employeeName' => $res['employeeName'],
                            'password' => $res['password'],
                            'idUserType' => $res['idUserType'],
                            'userTypeName' => $res['userTypeName'],
                            'token' => $token
                        ];

                        // self::setIdUser($res['idUser']);
                        // self::setToken($token);
                        // self::updateToken();

                        LogModel::newLog($res['userName'], Module::LOGIN, Action::LOGIN, 'Ingresó al sistema');
                        return ResponceHttp::status(ResponceHttp::STATUS_200, true, 'Operacion exitosa', $data);
                        exit;
                    } else {
                        return ResponceHttp::status(ResponceHttp::STATUS_401, false, "Contraseña incorrecta");
                    }
                }
            }
        } catch (\PDOException $e) {
            error_log('UserModel::getLogin -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo completar la operación')));
        }
    }

    final public static function getAll()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare(
                "SELECT
                    U.idUser,
                    U.userName,
                    U.employeeName,
                    U.created,
                    UT.name AS userTypeName,
                    U.status AS idStatus,
                    S.name AS statusName
                FROM 
                    user AS U
                    INNER JOIN status AS S ON S.idStatus = U.status
                    INNER JOIN userType AS UT ON UT.idUserType = U.userType
                AND U.status <> 99
                ORDER BY U.idUser"
            );
            $query->execute();
            $rs = $query->fetchAll(\PDO::FETCH_ASSOC);

            return ResponceHttp::status(ResponceHttp::STATUS_200, true, 'Operacion exitosa', $rs);
        } catch (\PDOException $e) {
            error_log('UserModel::getAll -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo completar la operación')));
        }
    }

    final public static function getUser()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("SELECT
                                        U.idUser,
                                        U.userName,
                                        U.password,
                                        U.employeeName,
                                        U.email,
                                        U.idCountryCode,
                                        U.phoneNumber,
                                        U.created,
                                        UT.idUserType,
                                        UT.name AS userTypeName,
                                        U.status AS idStatus,
                                        S.name AS statusName
                                    FROM 
                                        user AS U
                                        INNER JOIN status AS S ON S.idStatus = U.status
                                        INNER JOIN usertype AS UT ON UT.idUserType = U.usertype
                                    WHERE
                                        U.idUser = :idUser");
            $query->execute([
                'idUser' => self::getIdUser(),
            ]);

            if ($query->rowCount() === 0) {
                return ResponceHttp::status(ResponceHttp::STATUS_404,true,"No existe un usuario con este idUser");
            } else {
                $rs = $query->fetchAll(\PDO::FETCH_ASSOC);
                return ResponceHttp::status(ResponceHttp::STATUS_200,true, "Usuario obtenido correctamente", $rs);
            }
        } catch (\PDOException $e) {
            error_log('UserModel::getUser -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo completar la operación')));
        }
    }

    final public static function post()
    {
        try {
            if (self::getIdUser() == 0) {
                return self::createUser();
            } else {
                if (Sql::exists("SELECT * FROM	user WHERE idUser = :idUser", ":idUser", self::getIdUser())) {
                    return self::updateUser();
                } else {
                    return ResponceHttp::status(ResponceHttp::STATUS_404, false, "Este idUser no existe; No se puede actualizar");
                }
            }
        } catch (\PDOException $e) {
            error_log('UserModel::post -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo completar la operación')));
        }
    }

    final static function createUser()
    {
        self::setToken(hash('sha256', self::getUserName() . self::getIdUser()));
        self::setCreated(date('Y-m-d H:i:s'));

        $con = self::getConnection();
        $query1 = "INSERT INTO user (userName, password, employeeName, email, idCountryCode, phoneNumber, userType, created, token) VALUES";
        $query2 = "(:userName, :password, :employeeName, :email, :idCountryCode, :phoneNumber, :userType, :created, :token)";

        $query = $con->prepare($query1 . $query2);
        $query->execute([
            // ':idUser' => 0,
            ':userName' => self::getUserName(),
            // ':password' => Security::createPassword(self::getPassword()),
            ':password' => self::getPassword(),
            ':employeeName' => self::getEmployeeName(),
            ':email' => self::getEmail(),
            ':idCountryCode' => self::getIdCountryCode(),
            ':phoneNumber' => self::getPhoneNumber(),
            ':userType' => self::getIdUserType(),
            ':created' => self::getCreated(),
            ':token' => self::getToken()
        ]);

        if ($query->rowCount() > 0) {
            LogModel::newLog(self::getUserSesion(), Module::USER, Action::CREATE, 'Se creó al usuario: ' . self::getUserName());
            return ResponceHttp::status(ResponceHttp::STATUS_200, true, "Usuario creado correctamente");
        } else {
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo completar la operación')));
        }
    }

    final static function updateUser()
    {
        $con = self::getConnection();
        $query = "UPDATE user 
                SET 
                    userName=:userName, 
                    password=:password, 
                    employeeName=:employeeName, 
                    email=:email, 
                    idCountryCode=:idCountryCode, 
                    phoneNumber=:phoneNumber, 
                    userType=:userType 
                WHERE 
                    idUser=:idUser";

        $query = $con->prepare($query);
        $query->execute([
            ':idUser' => self::getIdUser(),
            ':userName' => self::getUserName(),
            // ':password' => Security::createPassword(self::getPassword()),
            ':password' => self::getPassword(),
            ':employeeName' => self::getEmployeeName(),
            ':email' => self::getEmail(),
            ':idCountryCode' => self::getIdCountryCode(),
            ':phoneNumber' => self::getPhoneNumber(),
            ':userType' => self::getIdUserType()
        ]);

        LogModel::newLog(self::getUserSesion(), Module::USER, Action::UPDATE, 'Se modificó el usuario: ' . self::getUserName());
        return ResponceHttp::status(ResponceHttp::STATUS_200, true, "Usuario modificado correctamente");
    }

    final static function updateToken()
    {
        $con = self::getConnection();
        $query = "UPDATE user SET token=:token WHERE idUser=:idUser";

        $query = $con->prepare($query);
        $query->execute([
            ':idUser' => self::getIdUser(),
            ':token' => self::getToken()
        ]);

        if ($query->rowCount() > 0) {
            return ResponceHttp::status(ResponceHttp::STATUS_200,true,'Token actualizado');
        } else {
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo completar la operación')));
        }
    }

    final public static function updateStatusUser()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("UPDATE user SET status = :status WHERE idUser = :idUser");
            $query->execute([
                ':status' => self::getStatus(),
                ':idUser' => self::getIdUser()
            ]);

            LogModel::newLog(self::getUserSesion(), Module::USER, Action::getActionSring(self::getStatus()), 'Se modificó el status del usuario:  ' .  self::getIdUser());
            return ResponceHttp::status(ResponceHttp::STATUS_200);
        } catch (\PDOException $e) {
            error_log('UserModel::updateStatusUser -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500, false, 'No se pudo completar la operación')));
        }
    }
}