<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Japloora\Authent;

/**
 * Description of AuthentBase
 *
 * @author drdam
 */
class AuthentBase
{
    public static function getDBRoute() {
        return JAPLOORA_DOC_ROOT . '/AuthentDB';
    }
    public static function getDBUser() {
        return self::getDBRoute() . '/User';
    }
    public static function getDBlog() {
        return self::getDBRoute() . '/Log';
    }

}
