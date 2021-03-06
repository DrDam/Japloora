<?php

namespace Japloora\Authent;

use Japloora\Authent;
use Japloora\Authent\AuthentBase;
use Japloora\Base;

class AuthentAccessLog extends Base
{
    private $loggers;
    
    public function __construct() {
        
        $this->discoverClasses('Logger');
        $classes = $this->getImplementation('Japloora\Authent\LoggerInterface');
        $loggers = [];
        
        foreach ($classes as $className) {
            $loggers[$className::getId()] = new $className();
        }
        $this->loggers = $loggers;
    }
    
    public function log($login, $method, $url, $code)
    {       
        $datas = [
            'timestamp' => time(),
            'user_login' => $login,
            'method' => $method,
            'url' => $url,
            'http_code' => $code
        ];
        
        if(count($this->loggers) > 0 ) {
            foreach($this->loggers as $logger_id => $logger_class) {
                $logger_class->log($datas);
            }
        }
        else {
            file_put_contents(AuthentBase::getDBlog(), implode(' -- ', array_values($datas)) . "\n", FILE_APPEND);
        }
    }
    
    public function flush() {
                
        if(count($this->loggers) > 0 ) {
            foreach($this->loggers as $logger_id => $logger_class) {
                $logger_class->flush();
            }
        }
        else {
            file_put_contents(AuthentBase::getDBlog(), '');
        }
    }
    
}
