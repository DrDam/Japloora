<?php

namespace Japloora\Authent;

class AuthentDataBase
{
    private $DBFile = JAPLOORA_DOC_ROOT . '/AuthentDB/DB';
    private $CacheDatas = array();
    private static $instance;


    public static function connexion()
    {
        if (self::$instance == null) {
            self::$instance = new AuthentDataBase();
        }
        return self::$instance;
    }
    
    protected function __construct()
    {
        if (!file_exists($this->DBFile)) {
            mkdir(JAPLOORA_DOC_ROOT . '/AuthentDB/');
            touch($this->DBFile);
        }
        $this->updateDataCache();
    }

    private function updateDataCache()
    {
        $datas = file_get_contents($this->DBFile);
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
        file_put_contents($this->DBFile, json_encode($this->CacheDatas));
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
            if ($datas->login == $login && $datas->pass == $pass) {
                return $id;
            }
        }
        return false;
    }
    
    public function getUser($user_id)
    {
        $this->updateDataCache();
        if (isset($this->CacheDatas[$user_id])) {
            $user = $this->CacheDatas[$user_id];
            unset($user->token);
            return $this->CacheDatas[$user_id];
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
        $user = $this->getUser($userId);

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
            if ($user->token === $token) {
                return $key;
            }
        }
        return null;
    }
}
