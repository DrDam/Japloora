<?php
/*
 * Initialisation of AuthentDB
 */

namespace Japloora\Authent\Init;

use Japloora\InitBase;
use Japloora\Authent\AuthentBase;
use Japloora\Authent\AuthentDataBase;
use Japloora\Authent\AuthentFactory;

class AuthentInit extends InitBase
{
    public static function initialize($conf)
    {
        // Create all files of DB
        if (!is_dir(AuthentBase::getDBRoute())) {
            mkdir(AuthentBase::getDBRoute());
        }
        
        if (!file_exists(AuthentBase::getDBUser())) {
            touch(AuthentBase::getDBUser());
        }
        
        if (!file_exists(AuthentBase::getDBLog())) {
            touch(AuthentBase::getDBLog());
        }
        
        // Create Super User
        $su = $conf['Authent'];
        
        $data = new \stdClass();
        $data->Login = $su['login'];
        $data->Pass = AuthentDataBase::hash($su['pass']);
        $data->Permissions = ['su'];

        $authentDB = AuthentFactory::connexion();
        $authentDB->write($data);
    }
}
