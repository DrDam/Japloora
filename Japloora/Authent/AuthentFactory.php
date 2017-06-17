<?php



namespace Japloora\Authent;

use Japloora\Base;
use Japloora\Authent\AuthentDataBase;

class AuthentFactory extends Base
{
    
    public static function connexion()
    {
        self::discoverClasses('AuthentData');
        
        $authent_db = self::getImplementation('Authent\AuthentData');
        
        if (count($authent_db) == 0) {
            return AuthentDataBase::connexion();
        } elseif (count($authent_db) == 1) {
            return $authent_db[0]::connexion();
        } else {
            // ???
        }
    }
}
