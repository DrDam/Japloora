<?php

namespace Japloora;

/**
 * Abstract class for all controler
 */
abstract class ControllerBase
{

    protected $parameters;

    public function setParameters($param) {
        $this->parameters = $param;
    }
    
    /**
     * Defined route binded by module
     */
    abstract public static function defineRoutes();
    
    /*
     * Callback exemple
     */
    /*
     * public function callback()
     */
}
