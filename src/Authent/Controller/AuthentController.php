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
                    'Site' => [],
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
                    'Site' => [],
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
        $user_id = $this->parameters['user_id'];
        if($user_id == NULL) {
            return [
                'datas' => '',
                'code' => 403,
            ];
        }
        
        $user = $this->authentDB->getUser($user_id);
        if ($user != null) {
             $this->authentDB->deleteUser($user_id);
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
        $user_id = $this->parameters['user_id'];
        $user = $this->authentDB->getUser($user_id);
        return array('datas' => $user);
    }

    public function addUser()
    {
        $new_user = $this->prepareUser($this->parameters['Query'], TRUE);
        $user_id = $this->authentDB->makeUser($new_user);

        return [
            'datas' => array("query" => $this->parameters['Query'], 'user_id' => $user_id),
            'code' => 201,
        ];
    }
    
    private function prepareUser($query, $create = false){
        $permissions = (isset($this->parameters['Query']['Permissions'])
        && is_array($this->parameters['Query']['Permissions']))
            ? $this->parameters['Query']['Permissions']
            : [];
        $new_user = new \stdClass();
        $new_user->Login = $this->parameters['Query']['Login'];
        $new_user->Permissions = $permissions;
        $new_user->Pass = $this->parameters['Query']['Pass'];
        $new_user->Site = $this->parameters['Query']['Site'];

        if($create === TRUE && $new_user->Permissions == []) {
            $new_user->Permissions = ['read'];
        }
        
        return $new_user;
    }

    public function updateUser()
    {
        $user_id = $this->parameters['user_id'];
        $user = $this->authentDB->getUser($user_id);
        if ($user != null) {
            $new_data = $this->prepareUser($this->parameters['Query']);
            $new_data->Id = $query[0];
            if($new_data->Permissions == []) {
                unset($new_data->Permissions);
            }
            $user_id = $this->authentDB->makeUser($new_data);
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
