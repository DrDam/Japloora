<?php

namespace Japloora\Authent;

use Japloora\Authent\AuthentBase;
use Japloora\Base;

class AuthentManager extends Base
{
    
    private $authent_db;
    private static $instance;

    /**
     * Connect to Authent DB
     * @return AuthentDataBase
     */
    public static function connexion()
    {
        if (self::$instance == null) {
            self::$instance = new AuthentManager();
        }
        return self::$instance;
    }
    /**
     * Refresh Database
     */
    protected function __construct()
    {
        $this->discoverClasses('AuthentData');
        
        $classes = $this->getImplementation('Japloora\Authent\AuthentDataInterface');
        if (count($classes) == 0) {
            $this->authent_db =  AuthentDataBase::connexion();
        } elseif (count($classes) == 1) {
            $this->authent_db =  $classes[0]::connexion();
        } else {
            // ???
        }
    }

    /**
     * Database Hash method
     * @param string $string
     * @return string
     */
    public function hash($string)
    {
        return md5($string);
    }

    /**
     * Check authentification token
     * @param string $token
     * @return array
     */
    public function checkToken($token)
    {
        $status = false;
        $message = '';
        //1 Get user
        $user = $this->getUserByToken($token);

        $user_id = $user->Id;
        
        if ($user_id !== null) {
            //2 Test if Token not expired
            $valid_token = $this->generateToken($user_id, false);
            if ($valid_token != null && $token === $valid_token['token']) {
                return ['status' => true, 'user_id' => $user_id];
            } else {
                $message = 'Exprired Token';
            }
        }
        return ['status' => $status, 'message' => $message];
    }

    
    
   /**
     * Generate authentification token
     * @param int $userId
     * @param bool $saveToken
     * @return array
     */
    public function generateToken($userId, $saveToken = true)
    {
        $user = $this->getUser($userId, true);

        $hash = $this->hash($user->Pass);
        $salt = floor(time() / 1000);
        $token = $this->hash($user->Login . $hash . $salt);

        if ($saveToken === true) {
            $user->Token = $token;
            $user->Id = $userId;
            $this->updateUser($user);
        }

        $token_data = ['token' => $token, 'expiration' => ($salt + 1) * 1000];
        return $token_data;
    }
    
    /**
     * Check if userId has Permission
     * @param type $user_id
     * @param type $permission
     * @return Boolean
     */
    public function userAccess($user_id, $permission)
    {
        $user = $this->getUser($user_id);
        return (in_array($permission, $user->Permissions));
    }

        /**
     * Check login/pass related
     * @param strin $login
     * @param strin $pass
     * @return boolean
     */
    public function authentify($login, $pass)
    {
        $user = $this->getUserByLogin($login);
        if ($user->Login == $login && $user->Pass == $this->hash($pass)) {
            return $user->Id;
        }
        return false;
    }
    
    
    public function makeUser($user) {
        var_dump($user);
        $user->Pass = $this->hash($user->Pass);
        $this->createUser($user);
    }
    
    
    /**************************
     * Call authent DB methods* 
     **************************/
    
   
    
    /**
     * Return User Data from id
     * @param type $user_id
     * @return type
     */
    public function getUser($user_id, $withPass = false)
    {
        return $this->authent_db->getUser($user_id, $withPass);
    }
    

     /**
     * Get All Users
     * @return array
     */
    public function getAllUsers()
    {
        return $this->authent_db->getAllUsers();
    }
    
     /**
     * Extract User who generate tocken
     * @param string $token
     * @return int|null
     */
    private function getUserByToken($token)
    {
        return $this->authent_db->getUserByToken($token);
    }
    
    public function getUserByLogin($login)
    {
        return $this->authent_db->getUserByLogin($login);
    }
    
    private function updateUser($user) {
        return $this->authent_db->updateUser($user);
    }

        
     /**
     * Delete a User Users
     * @return array
     */
    public function deleteUser($user_id)
    {
        return $this->authent_db->deleteUser($user_id);
    }
    
        /**
         * 
         * @param type $user
         * @return type
         */
    public function createUser($user) {
        return $this->authent_db->createUser($user);
    }
    
}
