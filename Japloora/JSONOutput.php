<?php

namespace Japloora;

/**
 * Description of JSONOutput
 *
 * @author drdam
 */
class JSONOutput
{
    /**
     * Output JSON
     * @param mixed $datas
     * @param int $http_code
     */
    public static function end($datas, $http_code = 200)
    {
        header('Content-Type: application/json');
        http_response_code($http_code);
        print json_encode($datas);
        exit;
    }
    
    /**
     * Output a 404 message
     */
    public static function send404()
    {
        $datas = [
            'error' => "Ressource Not Found",
        ];
        self::end($datas, 404);
    }
    
    /**
     * Output a 403 message
     * @param string $message
     */
    public static function send403($message = '')
    {
        
        $datas = [
            'error' => ($message != '') ? $message : "Your are not authorize to access on this ressource",
        ];
        self::end($datas, 403);
    }
}
