<?php

namespace Japloora\Authent;

use Japloora\Authent\AuthentBase;
use Japloora\Base;
use Firebase\JWT\JWT;

class AuthentManager extends Base
{
    
    private $authent_db;
    private static $instance;

    public static function checkToken($token) {
        
        $token_fragments = explode('.', $token);
        if(count($token_fragments) != 3) {
            // Flag Error
        }
        list($headb64, $bodyb64, $cryptob64) = $token_fragments;
        if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64))) {
            // Flag Error
        }

        $db = self::connexion();
        $user = $db->getUser($payload->use, TRUE);
        
        if($user == Null || $user->site != $payload->sub) {
            // Flag Error
        }

        $data = JWT::decode($token, $user->pass, ['HS256']);
    }
    
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
    
    
    public function makeUser($user) {
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
