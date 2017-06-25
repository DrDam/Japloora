<?php
/*
 * Data Class containing DB paths
 */

namespace Japloora\Authent;

class AuthentBase
{
    public static function getDBRoute()
    {
        return JAPLOORA_DOC_ROOT . '/AuthentDB';
    }
    public static function getDBUser()
    {
        return self::getDBRoute() . '/User';
    }
    public static function getDBlog()
    {
        return self::getDBRoute() . '/Log';
    }
}
