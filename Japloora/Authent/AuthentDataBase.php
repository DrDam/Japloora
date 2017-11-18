<?php

namespace Japloora\Authent;

use Japloora\Authent\AuthentBase;
use Japloora\Authent\AuthentManager;

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
        $this->updateDataCache();
        if (isset($datas->Id)) {
            $out = $this->update($datas);
        } else {
            $out = $this->insert($datas);
        }


        return $out;
    }

    public function updateUser($user) {
       $this->write($user);
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
        $datas->Id = $this->getFreeId();
        $this->CacheDatas[] = $datas;
        $this->writeDatas();
        return $datas->Id;
    }

    /**
     * Found free id in Database
     * @return int
     */
    private function getFreeId()
    {
        $all_keys = array_keys($this->getAllUsers());
        sort($all_keys);
        $start = 0;
        if (is_array($all_keys) && count($all_keys) > 0) {
            while (in_array($start, $all_keys)) {
                $start++;
            }
        }

        return $start;
    }

    /**
     * Phisical write method
     */
    private function writeDatas()
    {
        file_put_contents(AuthentBase::getDBUser(), json_encode($this->CacheDatas));
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
            if ($withPass === false) {
                unset($user->Pass);
            }
            $user->Id = $user_id;
            return $user;
        }
        return null;
    }
    
    public function getUserByLogin($login)
    {
        $this->updateDataCache();
        foreach($this->CacheDatas as $user_id => $user) {
            if($user->Login === $login) {
                unset($user->Token);
                $user->Id = $user_id;
                return $user;
            }
        }
        return null;
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
     * Get All Users
     * @return array
     */
    public function getAllUsers()
    {
        $this->updateDataCache();
        $collection = array();
        foreach (array_keys($this->CacheDatas) as $key) {
            $collection[$key] = ['Id' => $key];
        }
        return $collection;
    }

    public function deleteUser($user_id)
    {
        $this->updateDataCache();
        unset($this->CacheDatas[$user_id]);
        $this->writeDatas();
    }
    
    public function createUser($user) {
        $this->write($user);
    }
}
