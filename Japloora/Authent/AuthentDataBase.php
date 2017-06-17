<?php

namespace Japloora\Authent;

class AuthentDataBase
{

    static protected $instance;
    private static $DBFile;
    private $CacheDatas = array();

    public static function connexion()
    {
        self::$DBFile = JAPLOORA_DOC_ROOT . '/AuthentDB/DB';
        if (!file_exists(self::$DBFile)) {
            mkdir(JAPLOORA_DOC_ROOT . '/AuthentDB/');
            touch(self::$DBFile);
        }

        if (self::$instance == null) {
            self::$instance = new self();
            return self::$instance;
        }
    }

    protected function __construct()
    {
        $this->updateDataCache();
    }

    private function updateDataCache()
    {
        $datas = file_get_contents(self::$DBFile);
        $this->CacheDatas = json_decode($datas);
    }

    public function write($datas)
    {
        if (isset($datas->id)) {
            $this->update($datas);
        } else {
            $this->insert($datas);
        }

        $this->updateDataCache();
    }

    private function update($datas)
    {
        $this->updateDataCache();
        $id = $datas->id;
        unset($datas->id);
        $this->CacheDatas[$id] = $datas;
        $this->writeDatas();
    }

    private function insert($datas)
    {
        $this->CacheDatas[] = $datas;
        $this->writeDatas();
    }

    private function writeDatas()
    {
        file_put_contents(self::$DBFile, json_encode($this->CacheDatas));
    }

    public function authentifie($login, $pass)
    {
        foreach ($this->CacheDatas as $id => $datas) {
            if ($datas->login == $login && $datas->pass == $pass) {
                return $id;
            }
        }
        return false;
    }

    /**
     * @param $login
     * @param $pass
     * @return array
     */
    public function generateToken($userId, $saveToken = true)
    {
        $this->updateDataCache();

        $user = $this->CacheDatas[$userId];
        
        $md5 = md5($user->pass);
        $salt = floor(time() / 1000);
        $token = md5($user->login . $md5 . $salt);
        
        if ($saveToken === true) {
            $user->token = $token;
            $user->id = $userId;
            $this->write($user);
        }
        
        $token_data = ['token' => $token, 'expiration' => ($salt + 1) * 1000];
        return $token_data;
    }

    /**
     * @param $login
     * @param $token
     * @return bool
     */
    public static function checkToken($login, $token)
    {

        // Get User in DB
        $pass = "machin";

        $valid_token = self::generateToken($login, $pass);

        return ($token === $valid_token['token']);
    }
}
