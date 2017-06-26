<?php

namespace Japloora\Authent;

use Japloora\Authent\AuthentBase;

class AuthentDataBase
{
    private $CacheDatas = array();
    private static $instance;

    /**
     * Connect to Authent DB
     * @return AuthentDataBase
     */
    public static function connexion()
    {
        if (self::$instance == null) {
            self::$instance = new AuthentDataBase();
        }
        return self::$instance;
    }
    
    /**
     * Refresh Database
     */
    protected function __construct()
    {
        $this->updateDataCache();
    }
    
    /**
     * Database Hash method
     * @param string $string
     * @return string
     */
    public static function hash($string)
    {
        return md5($string);
    }

    /**
     * Refresh database Cache
     */
    private function updateDataCache()
    {
        $datas = file_get_contents(AuthentBase::getDBUser());
        $this->CacheDatas = json_decode($datas);
        if ($this->CacheDatas == null) {
            $this->CacheDatas = [];
        }
    }

    /**
     * Write data to Authent DB
     * @param type $datas
     * @return type
     */
    public function write($datas)
    {
        if (isset($datas->Id)) {
            $out = $this->update($datas);
        } else {
            $out = $this->insert($datas);
        }

        $this->updateDataCache();
        return $out;
    }

    /**
     * Update a User Data
     * @param type $datas
     * @return string
     */
    private function update($datas)
    {
        $this->updateDataCache();
        $id = $datas->Id;
        unset($datas->Id);
        $this->CacheDatas[$id] = $datas;
        $this->writeDatas();
        return '';
    }

    /**
     * Create new User
     * @param type $datas
     * @return type
     */
    private function insert($datas)
    {
        $datas->Id = count($this->CacheDatas);
        $this->CacheDatas[] = $datas;
        $this->writeDatas();
        return $datas->Id;
    }

    /**
     * Phisical write method
     */
    private function writeDatas()
    {
        file_put_contents(AuthentBase::getDBUser(), json_encode($this->CacheDatas));
    }

    /**
     * Check login/pass related
     * @param strin $login
     * @param strin $pass
     * @return boolean
     */
    public function authentify($login, $pass)
    {
        foreach ($this->CacheDatas as $id => $datas) {
            if ($datas->Login == $login && $datas->Pass == self::hash($pass)) {
                return $id;
            }
        }
        return false;
    }
    
    /**
     * Return User Data from id
     * @param type $user_id
     * @return type
     */
    public function getUser($user_id, $withPass = false)
    {
        $this->updateDataCache();
        if (isset($this->CacheDatas[$user_id])) {
            $user = $this->CacheDatas[$user_id];
            unset($user->Token);
            if($withPass === false) {
                unset($user->Pass);
            }
            $user->Id = $user_id;
            return $user;
        }
        return null;
    }

    /**
     * Generate authentification token
     * @param int $userId
     * @param bool $saveToken
     * @return array
     */
    public function generateToken($userId, $saveToken = true)
    {
        $user = $this->getUser($userId, TRUE);

        $hash = self::hash($user->Pass);
        $salt = floor(time() / 1000);
        $token = self::hash($user->Login . $hash . $salt);

        if ($saveToken === true) {
            $user->Token = $token;
            $user->Id = $userId;
            $this->write($user);
        }

        $token_data = ['token' => $token, 'expiration' => ($salt + 1) * 1000];
        return $token_data;
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
        $user_id = $this->getUserByToken($token);

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
     * Extract User who generate tocken
     * @param string $token
     * @return int|null
     */
    private function getUserByToken($token)
    {
        foreach ($this->CacheDatas as $key => $user) {
            if ($user->Token === $token) {
                return $key;
            }
        }
        return null;
    }
    
    /**
     * Check if userId has Permission
     * @param type $user_id
     * @param type $permission
     * @return Boolean
     */
    public function userAccess($user_id, $permission) {
        $user = $this->getUser($user_id);
        return (in_array($permission, $user->Permissions));
    }
    
    /**
     * Get All Users
     * @return array
     */
    public function getAllUsers() {
        $this->updateDataCache();
        $collection = array();
        foreach(array_keys($this->CacheDatas) as $key) {
           $collection[$key] = ['Id' => $key];
        }
        
        return $collection;
    }
}
