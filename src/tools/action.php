<?php

namespace App\Tools;

class Action
{
    const CREATE = 'Crear';
    const UPDATE = 'Modificar';
    const DELETE = 'Eliminar';
    const LOGIN = 'Login';
    const ENABLE = 'Activar';
    const DISABLE = 'Desactivar';
    const UNSHOW = 'Ocultar';
    const MAIL = 'Correo';

    final public static function getActionSring($status)
    {
        switch ($status) {
            case Status::ENABLED:
                return self::ENABLE;
            case Status::DISABLED:
                return self::DISABLE;
            case Status::DELETED:
                return self::DELETE;
        }
    }
}