<?php

namespace Japloora\Authent;

use Japloora\Authent\AuthentBase;
use Japloora\Base;
use Firebase\JWT\JWT;

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
     * Check JWT tocken for identification
     * 
     * @param type $token
     * @return type
     */
    public static function checkToken($token) {
        
        // Pre decode Token getting User_login
        $token_fragments = explode('.', $token);
        if(count($token_fragments) != 3) {
            // Flag Error
        }
        list($headb64, $bodyb64, $cryptob64) = $token_fragments;
        if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64))) {
            // Flag Error
        }
        
        // Get user Password from DB
        $db = self::connexion();
        $probable_user = $db->getUserByLogin($payload->use, TRUE);      
        if($probable_user == Null || $probable_user->Site != $payload->sub) {
            // Flag Error
        }

        // Try to decode token
        try{
            $data = JWT::decode($token, $probable_user->Pass, ['HS256']);
        } catch (Exception $e) {
            return ['user_id' => NULL, 'user_perm' => [], 'message' => $e->getMessage()];
        }

        // if there is no error on token decode
        if($data == $payload) {
            return ['user_id' => $probable_user->Id, 'user_perm' => $probable_user->Permissions, 'message' => ""];
        }    
    }
    
    /**
     * Database Hash method
     * @param string $string
     * @return string
     */
    public function hash($string)
    {
        return hash('sha256', $string);
    }
    
    /**
     * Check if userId has Permission
     * @param type $user_permissions
     * @param type $permission
     * @return Boolean
     */
    public static function userAccess($user_permissions, $permission)
    {
        return (in_array($permission, $user_permissions));
    }
    
    /**
     * Prepare date for creating User
     * 
     * @param type $user
     */
    public function makeUser($user) {
        $user->Pass = $this->hash($user->Pass);
        if(isset($user->Id)) {
            return $this->updateUser($user);
        }
        else {
            return $this->createUser($user);
        }
    }
    

    
    
    /**************************
     * Call authent DB methods* 
     **************************/
        
     /**
     * Get All Users
     * @return array
     */
    public function getAllUsers()
    {
        return $this->authent_db->getAllUsers();
    }
        
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
     * Get a Specific user from it's login
     * @param type $login
     * @return type
     */
    public function getUserByLogin($login)
    {
        return $this->authent_db->getUserByLogin($login);
    }
    
    /**
     * Update a user
     * @param type $user
     * @return type
     */
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
