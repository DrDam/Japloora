<?php

namespace Japloora\Authent;

use Japloora\Authent\AuthentBase;

class AuthentDataBase
{
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

        $this->updateDataCache();
    }
    
    public static function hash($string) {
        return md5($string);
    }

    private function updateDataCache()
    {
        $datas = file_get_contents(AuthentBase::getDBUser());
        $this->CacheDatas = json_decode($datas);
        if($this->CacheDatas == null) {
            $this->CacheDatas = [];
        }
    }

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

    private function update($datas)
    {
        $this->updateDataCache();
        $id = $datas->Id;
        unset($datas->Id);
        $this->CacheDatas[$id] = $datas;
        $this->writeDatas();
        return '';
    }

    private function insert($datas)
    {
        $datas->Id = count($this->CacheDatas);
        $this->CacheDatas[] = $datas;
        $this->writeDatas();
        return $datas->Id;
    }

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
            if ($user->token === $token) {
                return $key;
            }
        }
        return null;
    }
}
