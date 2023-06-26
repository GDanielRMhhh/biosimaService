<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\db\ConectionDB;
use App\DB\Sql;
use App\Tools\Action;
use App\Tools\Module;

class SettingModel extends ConectionDB
{
    private static int $idSetting;
    private static string $name;
    private static string $notificationTime;
    private static int $marginDays;
    private static string $customerMessage;
    private static string $employeeMessage;
    private static string $notificationContacts;
    private static string $created;

    private static string $userSesion;

    public function __construct(array $data)
    {
        // self::$idSetting = $data['idSetting'];
        // self::$name = $data['name'];
        self::$notificationTime = $data['notificationTime'];
        self::$marginDays = $data['marginDays'];
        self::$customerMessage = $data['msnClient'];
        self::$employeeMessage = $data['msnBiosima'];
        self::$notificationContacts = $data['notificationContacts'];
        // self::$created = $data['created'];

        self::$userSesion = $data['userSesion'];
    }
    
    final public static function getIdSetting() { return self::$idSetting; }
    // final public static function getName() { return self::$name; }
    final public static function getNotificationTime() { return self::$notificationTime; }
    final public static function getMarginDays() { return self::$marginDays; }
    final public static function getCustomerMessage() { return self::$customerMessage; }
    final public static function getEmployeeMessage() { return self::$employeeMessage; }
    final public static function getNotificationContacts() { return self::$notificationContacts; }
    // final public static function getCreated() { return self::$created; }
    final public static function getUserSesion() { return self::$userSesion; }
    
    final public static function setIdSetting(int $idSetting) { self::$idSetting = $idSetting; }
    // final public static function setName(string $name) { self::$name = $name; }
    final public static function setNotificationTime(string $notificationTime) { self::$notificationTime = $notificationTime; }
    final public static function setMarginDays(int $marginDays) { self::$marginDays = $marginDays; }
    final public static function setCustomerMessage(string $customerMessage) { self::$customerMessage = $customerMessage; }
    final public static function setEmployeeMessage(string $employeeMessage) { self::$employeeMessage = $employeeMessage; }
    final public static function setNotificationContacts(string $notificationContacts) { self::$notificationContacts = $notificationContacts; }
    // final public static function setCreated(string $created) { self::$created = $created; }
    final public static function setUserSesion(string $userSesion) { self::$userSesion = $userSesion; }

    final public static function getSetting()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("SELECT
                                        idSetting,
                                        notificationTime,
                                        marginDays,
                                        employeeMessage,
                                        customerMessage,
                                        notificationContacts
                                    FROM setting
                                    LIMIT 1"
            );
            $query->execute();
            
            $rs = $query->fetch(\PDO::FETCH_ASSOC);
            
            return ResponceHttp::status(ResponceHttp::STATUS_200,true,'Configuracion obtenida correctamente',$rs);
        } catch (\PDOException $e) {
            error_log('SettingModel::getConfiguration -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false, "No se pueden obtener los datos")));
        }
    }

    final public static function post()
    {
        try {
            self::setIdSetting(1);
            if (Sql::exists("SELECT * FROM setting WHERE idSetting = :idSetting", ":idSetting", self::getIdSetting())) {
                return self::update();
            } else {
                return ResponceHttp::status(ResponceHttp::STATUS_400,false,"Este idSetting no existe; No se puede actulizar");
            }
        } catch (\PDOException $e) {
            error_log('SettingModel::post -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false,'No se pudo completar la operación')));
        }
    }

    final static function update()
    {
        $con = self::getConnection();
        $query = "UPDATE setting 
                    SET 
                        notificationTime=:notificationTime,
                        marginDays=:marginDays,
                        customerMessage=:customerMessage,
                        employeeMessage=:employeeMessage,
                        notificationContacts=:notificationContacts";

        $query = $con->prepare($query);
        $query->execute([
            ':notificationTime' => self::getNotificationTime(),
            ':marginDays' => self::getMarginDays(),
            ':customerMessage' => self::getCustomerMessage(),
            ':employeeMessage' => self::getEmployeeMessage(),
            ':notificationContacts' => self::getNotificationContacts()
        ]);
        
        LogModel::newLog(self::getUserSesion(), Module::SETTING, Action::UPDATE, 'Se modificaron los parametros de configuración');
        return ResponceHttp::status(ResponceHttp::STATUS_200);
    }
}