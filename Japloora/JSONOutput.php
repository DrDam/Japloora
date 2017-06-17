<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Japloora;

/**
 * Description of JSONOutput
 *
 * @author drdam
 */
class JSONOutput
{
    static public function end($datas = [], $http_code) {
        header('Content-Type: application/json');
        http_response_code($http_code);
        print json_encode($datas);
        exit;
    }
    
    static public function send404() {
        $datas = [
            'error' => "Ressource Not Found",
        ];
        self::end($datas, 404);
    }
    
    static public function send403() {
        $datas = [
            'error' => "Your are not authorize to access on this ressource",
        ];
        self::end($datas, 404);
    }
}
