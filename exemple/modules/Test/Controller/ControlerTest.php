<?php

namespace Test;

use Japloora\ControlerBase;

class ControlerTest extends ControlerBase
{

    /**
     *
     * definition of a route :
     * [
     *  path => url, ( required)
     *  callback => myMethod, ( required)
     *  scheme => [ROUTE_PARAMETER_SCHEME_HTTP], ( optional, default : HTTP)
     *  method => [ROUTE_PARAMETER_METHOD_GET], ( optional, default : GET)
     *  authent => [permission => PERMISION], ( optional, default : none)
     *  parameters => [                 ( optional, default : none)
            Param1 => [],           ( default : required, type string)
            Param2 => [
                        'mandatory' => ROUTE_PARAMETER_OPTIONAL,
                        'type' => ROUTE_PARAMETER_TYPE_ARRAY
                    ],
                ],
     * ]
     *
     * scheme : multiple
        ROUTE_PARAMETER_SCHEME_HTTP => http (default)
        ROUTE_PARAMETER_SCHEME_HTTPS => https
     * method : multiple
        ROUTE_PARAMETER_METHOD_GET => GET (default)
        ROUTE_PARAMETER_METHOD_POST => POST
        ROUTE_PARAMETER_METHOD_PATCH => PATCH
        ROUTE_PARAMETER_METHOD_DELETE => DELETE
     * authent : optional
     *      If defined, permision is checked
     * parameters : optional
     *      Mandatory :
                ROUTE_PARAMETER_REQUIRED => required (default)
                ROUTE_PARAMETER_OPTIONAL => optional
     *      data type :
                ROUTE_PARAMETER_TYPE_STRING => string (default)
                ROUTE_PARAMETER_TYPE_INT => integer
                ROUTE_PARAMETER_TYPE_NUM => numeric
                ROUTE_PARAMETER_TYPE_BOOL => bool
                ROUTE_PARAMETER_TYPE_ARRAY => array
     *
     */
    public static function defineRoutes()
    {
        return array(
            array(
                'path' => 'exemple1',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTPS],
                'method' => [ROUTE_PARAMETER_METHOD_POST],
                'callback' => 'test',
            ),
            array(
                'strict' => false,
                'path' => 'toto/*',
                'parameters' => array(
                    'Param1' => [],
                    'Param2' => [],
                ),
                'callback' => 'testParameters'
            ),
            // Most accurate route
            array(
              'strict' => false,
              'path' => 'toto/titi',
              'authent' => ['permission' => 'read'],
              'callback' => 'test'
            )
        );
    }

    public function test($params)
    {
        // JSON OutPut need JSON Encodable Variable
        return ['datas' => ["toto" => $params]];
    }

    public function testParameters($params, $path)
    {
      // $params = ['Param1' => "value param" , 'Param2' => "value param" ];
        return array('datas' => ["out" => $params]);
    }
}
