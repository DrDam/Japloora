<?php

namespace Japloora\Authent\Controler;

use Japloora\ControlerBase;
use Japloora\JSONOutput;
use Japloora\Authent\AuthentFactory;

class AuthentControler extends ControlerBase
{

    public static function defineRoutes()
    {
        return array(
            array(
                'path' => 'authent',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'method' => [ROUTE_PARAMETER_METHOD_POST],
                'parameters' => [
                    'Login' => [],
                    'Pass' => [],
                ],
                'callback' => 'generateToken',
            ),
            array(
                'path' => 'users',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'Authent' => ['permission' => 'su'],
                'callback' => 'getUsers',
            ),
            array(
                'path' => 'user/*',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'Authent' => ['permission' => 'su'],
                'callback' => 'getUser',
            ),
            array(
                'path' => 'user/add',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'method' => [ROUTE_PARAMETER_METHOD_POST],
                'Authent' => ['permission' => 'su'],
                'callback' => 'addUser',
                'parameters' => [
                    'Login' => [],
                    'Pass' => [],
                    'Permissions' => [
                        'mandatory' => ROUTE_PARAMETER_OPTIONAL,
                        'type' => ROUTE_PARAMETER_TYPE_ARRAY
                    ],
                ],
            ),
        );
    }

    /**
     * Return Token to User
     * @param type $params
     * @return type
     */
    public function generateToken($params)
    {
        $authentDB = AuthentFactory::connexion();

        $pass = $params['Query']['Pass'];
        $login = $params['Query']['Login'];

        $userId = $authentDB->authentify($login, $pass);
        if ($userId === false) {
            JSONOutput::send403();
        }

        $token_data = $authentDB->generateToken($userId);

        return array('datas' => ["token" => $token_data['token'], 'expiration' => $token_data['expiration']]);
    }

    public function getUsers($params)
    {

        $authentDB = AuthentFactory::connexion();
        $users = $authentDB->getAllUsers();

        return array('datas' => $users);
    }

    public function getUser($params)
    {

        $query = $params['queryFragments'];

        $authentDB = AuthentFactory::connexion();
        $user = $authentDB->getUser($query[0]);

        return array('datas' => $user);
    }

    public function addUser($params)
    {
        $db = AuthentFactory::connexion();
        
        $permissions = (isset($params['Query']['Permissions']) && is_array($params['Query']['Permissions']))
                ? $params['Query']['Permissions']
                : ['read'];
        $new_user = new \stdClass();
        $new_user->Login = $params['Query']['Login'];
        $new_user->Permissions = $permissions;
        $new_user->Pass = $db::hash($params['Query']['Pass']);
   
        $user_id = $db->write($new_user);

        return [
            'datas' => array("query" => $params['Query'], 'user_id' => $user_id),
            'code' => 201,
        ];
    }
}
