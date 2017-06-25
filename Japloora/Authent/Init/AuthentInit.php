<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Japloora\Authent\Init;

use Japloora\InitBase;
use Japloora\Authent\AuthentBase;
use Japloora\Authent\AuthentDataBase;
use Japloora\Authent\AuthentFactory;
/**
 * Description of AuthentInit
 *
 * @author drdam
 */
class AuthentInit extends InitBase
{
    public static function initialize($conf) {
        
        if(!is_dir(AuthentBase::getDBRoute())) {
            mkdir(AuthentBase::getDBRoute());
        }
        
        if (!file_exists(AuthentBase::getDBUser())) {
            touch(AuthentBase::getDBUser());
        }
        
        if (!file_exists(AuthentBase::getDBLog())) {
            touch(AuthentBase::getDBLog());
        }
        
        $su = $conf['Authent'];
        
        $data = new \stdClass();
        $data->Login = $su['login'];
        $data->Pass = AuthentDataBase::hash($su['pass']);
        $data->Permissions = ['su'];

        $authentDB = AuthentFactory::connexion();
        $authentDB->write($data);
    }
}
