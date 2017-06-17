<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Japloora;

/**
 * Description of Watchdog
 *
 * @author drdam
 */
class Watchdog
{
    //put your code here
    
    public static function write($message, $type = NULL) {
        $string = ($type != NULL) ? $type . " :: " . $message : $message;
        return error_log($string);
    }
}
