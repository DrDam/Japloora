<?php

namespace Japloora\Authent\Controller;

use Japloora\ControllerBase;
use Japloora\JSONOutput;
use Japloora\Authent\AuthentManager;
use Japloora\Authent\AuthentAccessLog;

class AuthentController extends ControllerBase
{
    
    private $authentDB;

    public function __construct() {
        $this->authentDB = AuthentManager::connexion();
    }
    
    public static function defineRoutes()
    {
        return array(
            array(
                'path' => 'users',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'authent' => ['permission' => 'su'],
                'callback' => 'getUsers',
            ),
            array(
                'path' => 'user/*',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'authent' => ['permission' => 'su'],
                'callback' => 'getUser',
            ),
            array(
                'path' => 'user/*',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'method' => [ROUTE_PARAMETER_METHOD_DELETE],
                'authent' => ['permission' => 'su'],
                'callback' => 'deleteUser',
            ),
            array(
                'path' => 'user/*',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'method' => [ROUTE_PARAMETER_METHOD_PATCH],
                'authent' => ['permission' => 'su'],
                'callback' => 'updateUser',
                'parameters' => [
                    'Login' => [],
                    'Pass' => [],
                    'Permissions' => [
                        'mandatory' => ROUTE_PARAMETER_OPTIONAL,
                        'type' => ROUTE_PARAMETER_TYPE_ARRAY
                    ],
                ],
            ),
            array(
                'path' => 'user/add',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'method' => [ROUTE_PARAMETER_METHOD_POST],
                'authent' => ['permission' => 'su'],
                'callback' => 'addUser',
                'parameters' => [
                    'Login' => [],
                    'Pass' => ['mandatory' => ROUTE_PARAMETER_OPTIONAL,],
                    'Permissions' => [
                        'mandatory' => ROUTE_PARAMETER_OPTIONAL,
                        'type' => ROUTE_PARAMETER_TYPE_ARRAY
                    ],
                ],
            ),
            array(
                'path' => 'flush',
                'scheme' => [ROUTE_PARAMETER_SCHEME_HTTP, ROUTE_PARAMETER_SCHEME_HTTPS],
                'method' => [ROUTE_PARAMETER_METHOD_DELETE],
                'authent' => ['permission' => 'su'],
                'callback' => 'flushLog',
            ),
        );
    }

    public function getUsers()
    {
        $users = $this->authentDB->getAllUsers();

        return array('datas' => $users);
    }

    public function deleteUser()
    {
        $query = $this->parameters['queryFragments'];

        $user = $this->authentDB->getUser($query[0]);

        if ($user != null && $query[0] != NULL) {
             $this->authentDB->deleteUser($query[0]);
            return [
                'datas' => '',
                'code' => 204,
            ];
        } else {
            return [
                'datas' => '',
                'code' => 403,
            ];
        }
    }

    public function getUser()
    {

        $query = $this->parameters['queryFragments'];

        $user = $this->authentDB->getUser($query[0]);

        return array('datas' => $user);
    }

    public function addUser()
    {
        $permissions = (isset($this->parameters['Query']['Permissions'])
                && is_array($this->parameters['Query']['Permissions']))
                    ? $this->parameters['Query']['Permissions']
                    : ['read'];
        $new_user = new \stdClass();
        $new_user->Login = $this->parameters['Query']['Login'];
        $new_user->Permissions = $permissions;
        $new_user->Pass = $this->parameters['Query']['Pass'];

        $user_id = $this->authentDB->makeUser($new_user);

        return [
            'datas' => array("query" => $this->parameters['Query'], 'user_id' => $user_id),
            'code' => 201,
        ];
    }

    public function updateUser()
    {
        $query = $this->parameters['queryFragments'];

        $user = $this->authentDB->getUser($query[0]);

        if ($user != null) {
            $new_user = $this->makeUser($this->parameters);
            $new_user->Id = $query[0];
            $user_id = $this->authentDB->write($new_user);
            return [
                'datas' => array("query" => $this->parameters['Query'], 'user_id' => $user_id),
                'code' => 201,
            ];
        }
    }
    
    public function flushLog() {
        $this->logger = new AuthentAccessLog();
        $this->logger->flush();
    }
}
