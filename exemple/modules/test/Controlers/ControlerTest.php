<?php

namespace CNF;

use Japloora\ControlerBase;
class ControlerTest extends ControlerBase {

    static function defineRoutes() {
        return array(
            array(
                'path' => 'toto',
                'scheme' => 'http',
                'method' => 'POST',
                'callback' => 'testPost',
                'output' => 'JSON'
            ),
            array(
                'path' => 'toto',
                'scheme' => 'http',
                'method' => 'GET',
                'callback' => 'testGet',
                'output' => 'HTML'
            ),
            array(
                'strict' => FALSE,
                'path' => 'toto/*',
                'scheme' => 'http',
                'method' => 'GET',
                'parameters' => array(
                    'Param1',
                    'Param2',
                ),
                'callback' => 'testParameters'
            ),
            // Most accurate route
            array(
              'strict' => FALSE,
              'path' => 'toto/titi',
              'scheme' => 'http',
              'method' => 'GET',
              'callback' => 'testPost'
            )
        );
    }

    public function testPost($params) {
        // JSON OutPut need JSON Encodable Variable
        return ['datas' => ["toto" => $params]];
    }

    public function testGet($params) {
        // HTML Output Aren't transformations
        return "<h2> Hello World </h2>";
    }

    public function testParameters($params, $path) {
      // $params = ['Param1' => "value param" , 'Param2' => "value param" ];
      return array('datas' => ["out" => $params]);
    }

}
