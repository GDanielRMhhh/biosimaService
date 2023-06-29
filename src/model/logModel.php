<?php

namespace App\Model;

use App\Db\ConectionDB;

class LogModel extends ConectionDB
{
    private static int $idLog;
    private static string $userName;
    private static string $module;
    private static string $action;
    private static string $description;
    private static string $date;

    public function __construct(array $data)
    {
        self::$idLog = $data['idLog'];
        self::$userName = $data['userName'];
        self::$module = $data[' module'];
        self::$action = $data['action'];
        self::$description = $data['description'];
        self::$date = $data['date'];
    }
    
    final public static function getIdLog() { return self::$idLog; }
    final public static function getUserName() { return self::$userName; }
    final public static function getModule() { return self::$module; }
    final public static function getAction() { return self::$action; }
    final public static function getDescription() { return self::$description; }
    final public static function getDate() { return self::$date; }
    
    final public static function setIdLog(int $idLog) { self::$idLog = $idLog; }
    final public static function setUserName(string $userName) { self::$userName = $userName; }
    final public static function setModule(string $module) { self::$module = $module; }
    final public static function setAction(string $action) { self::$action = $action; }
    final public static function setDescription(string $description) { self::$description = $description; }
    final public static function setDate(string $date) { self::$date = $date; }

    final static function newLog($userName, $module, $action, $description) {
        self::setUserName($userName);
        self::setModule($module);
        self::setAction($action);
        self::setDescription($description);
        self::setDate(date('Y-m-d H:i:s'));

        self::create();
    }

    final static function create()
    {
        $con = self::getConnection();
        $query1 = "INSERT INTO log (userName, module, action, description, date) VALUES";
        $query2 = "(:userName, :module, :action, :description, :date)";

        $query = $con->prepare($query1 . $query2);
        $query->execute([
            ':userName' => self::getUserName(),
            ':module' => self::getModule(),
            ':action' => self::getAction(),
            ':description' => self::getDescription(),
            ':date' => self::getDate()
        ]);
    }
}