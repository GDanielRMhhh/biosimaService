<?php

namespace App\Model;

use App\Config\ResponceHttp;
use App\Config\Security;
use App\Db\ConectionDB;
use App\Db\Sql;
use App\Tools\Action;
use App\Tools\Module;

class ContactModel extends ConectionDB
{
    private static int $idContact;
    private static string $name;
    private static int $idCountry;
    private static string $phone;
    private static string $email;
    private static int $contactType;
    private static int $idProcedure;
    private static string $created;
    private static int $createdBy;

    private static string $userSesion;

    public function __construct(array $data)
    {
        self::$idContact = $data['idContact'];
        self::$name = $data['name'];
        self::$idCountry = $data['idCountry'];
        self::$phone = $data['phone'];
        self::$email = $data['email'];
        self::$contactType = $data['contactType'];
        self::$idProcedure = $data['idProcedure'];
        self::$createdBy = $data['createdBy'];

        self::$userSesion = $data['userSesion'];
    }
    
    final public static function getIdContact() { return self::$idContact; }
    final public static function getName() { return self::$name; }
    final public static function getIdCountry() { return self::$idCountry; }
    final public static function getPhone() { return self::$phone; }
    final public static function getEmail() { return self::$email; }
    final public static function getContactType() { return self::$contactType; }
    final public static function getIdProcedure() { return self::$idProcedure; }
    final public static function getCreated() { return self::$created; }
    final public static function getCreatedBy() { return self::$createdBy; }
    final public static function getUserSesion() { return self::$userSesion; }
    
    final public static function setIdContact(int $idContact) { self::$idContact = $idContact; }
    final public static function setName(string $name) { self::$name = $name; }
    final public static function setIdCountry(int $idCountry) { self::$idCountry = $idCountry; }
    final public static function setPhone(string $phone) { self::$phone = $phone; }
    final public static function setEmail(string $email) { self::$email = $email; }
    final public static function setContactType(int $contactType) { self::$contactType = $contactType; }
    final public static function setIdProcedure(int $idProcedure) { self::$idProcedure = $idProcedure; }
    final public static function setCreated(string $created) { self::$created = $created; }
    final public static function setCreatedBy(string $createdBy) { self::$createdBy = $createdBy; }
    final public static function setUserSesion(string $userSesion) { self::$userSesion = $userSesion; }

    final public static function post()
    {
        try {
            if (self::getIdContact() == 0) {
                return self::create();
            } else {
                if (Sql::exists("SELECT * FROM contact WHERE idContact = :idContact", ":idContact", self::getIdContact())) {
                    return self::update();
                } else {
                    return ResponceHttp::status(ResponceHttp::STATUS_400,false,"Este idContact no existe; No se puede actulizar");
                }
            }
        } catch (\PDOException $e) {
            error_log('ContactModel::post -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false,'No se pudo completar la operacion')));
        }
    }

    final static function create()
    {
        self::setCreated(date('Y-m-d H:i:s'));

        $con = self::getConnection();
        $query1 = "INSERT INTO contact (name, idCountry, phone, email, contactType, idProcedure, created, createdBy) VALUES";
        $query2 = "(:name, :idCountry, :phone, :email, :contactType, :idProcedure, :created, :createdBy)";

        $query = $con->prepare($query1 . $query2);
        $query->execute([
            ':name' => self::getName(),
            ':idCountry' => self::getIdCountry(),
            ':phone' => self::getPhone(),
            ':email' => self::getEmail(),
            ':contactType' => self::getContactType(),
            ':idProcedure' => self::getIdProcedure(),
            ':created' => self::getCreated(),
            ':createdBy' => self::getCreatedBy()
        ]);

        if ($query->rowCount() > 0) {
            $idContactCreated = $con->lastInsertId();

            LogModel::newLog(self::getUserSesion(), Module::PROCEDURE, Action::CREATE, 'Se creó contacto con ID: ' . $idContactCreated . ', Nombre: ' . self::getName() . ', ID Tramite: ' . self::getIdProcedure());
            return ResponceHttp::status(ResponceHttp::STATUS_200,true,"Contacto creado correctamente");
        } else {
            return ResponceHttp::status(ResponceHttp::STATUS_500,false,'No se pudo completar la operacion');
        }
    }

    final static function update()
    {
        $con = self::getConnection();
        $query = "UPDATE contact 
                    SET 
                        name=:name, 
                        idCountry=:idCountry,
                        phone=:phone,
                        email=:email,
                        contactType=:contactType,
                        idProcedure=:idProcedure
                    WHERE 
                        idContact=:idContact";

        $query = $con->prepare($query);
        $query->execute([
            ':name' => self::getName(),
            ':idCountry' => self::getIdCountry(),
            ':phone' => self::getPhone(),
            ':email' => self::getEmail(),
            ':contactType' => self::getContactType(),
            ':idProcedure' => self::getIdProcedure(),
            ':idContact' => self::getIdContact()
        ]);

        LogModel::newLog(self::getUserSesion(), Module::PROCEDURE, Action::CREATE, 'Se modificó contacto con ID: ' . self::getIdContact() . ', Nombre: ' . self::getName() . ', ID Tramite: ' . self::getIdProcedure());
        return ResponceHttp::status(ResponceHttp::STATUS_200,true,'Contacto modificado correctamente');
    }

    final public static function delete()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("DELETE FROM contact WHERE idContact = :idContact");
            $query->execute([
                ':idContact' => self::getIdContact()
            ]);

            LogModel::newLog(self::getUserSesion(), Module::PROCEDURE, Action::DELETE, 'Se eliminó el contacto con ID: ' . self::getIdContact() . ', Nombre: ' . self::getName() . ', ID Tramite: ' . self::getIdProcedure());
        } catch (\PDOException $e) {
            error_log('ContactModel::delete -> ' . $e);
            die(json_encode(ResponceHttp::status(ResponceHttp::STATUS_500,false,'No se pudo eliminar el contacto')));
        }
    }
}