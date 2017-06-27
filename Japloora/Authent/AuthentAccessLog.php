<?php

namespace Japloora\Authent;

use Japloora\Authent;
use Japloora\Authent\AuthentBase;

class AuthentAccessLog
{
    public static function write($user_id, $url, $code)
    {
        
        $datas = [
            time(),
            $user_id,
            $url,
            $code
        ];
        
        file_put_contents(AuthentBase::getDBlog(), implode(' -- ', $datas) . "\n", FILE_APPEND);
    }
}
