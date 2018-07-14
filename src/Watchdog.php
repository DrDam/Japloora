<?php

namespace Japloora;

/**
 * Description of Watchdog
 *
 * @author drdam
 */
class Watchdog
{

    /**
     * Generic Debug Output
     * @param type $message
     * @param type $type
     */
    public static function write($message, $type = null)
    {
        $string = ($type != null) ? $type . " :: " . $message : $message;
        error_log($string);
    }
}
