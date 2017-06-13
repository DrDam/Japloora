<?php

namespace CNF;

use Japloora\ControlerBase;
class ControlerTest extends ControlerBase {

    static function defineRoutes() {
        return array(
            array(
                'path' => 'toto',
                'sheme' => 'http',
                'method' => 'POST',
                'callback' => 'testPost',
                'output' => 'JSON'
            ),
            array(
                'path' => 'toto',
                'sheme' => 'http',
                'method' => 'GET',
                'callback' => 'testGet',
                'output' => 'HTML'
            ),
            array(
                'strict' => FALSE,
                'path' => 'toto/*',
                'sheme' => 'http',
                'method' => 'GET',
                'parameters' => array(
                    'Param1',
                    'Param2',
                ),
                'callback' => 'testParameters'
            )
        );
    }

    public function testPost($params) {
        // JSON OutPut need JSON Encodable Variable
        return array("toto" => $params);
    }

    public function testGet($params) {
        // HTML Output Aren't transformations
        return "<h2> Hello World </h2>";
    }

    public function testParameters($params) {
        // $params = ['Param1' => "value param" , 'Param2' => "value param" ];
        return "<h2> Defaut output is HTML </h2>";
    }

}
