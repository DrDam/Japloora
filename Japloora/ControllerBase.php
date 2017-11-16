<?php

namespace Japloora;

/**
 * Abstract class for all controler
 */
abstract class ControllerBase
{

    /**
     * Datas extract from HttpFoundation Request
     * @var array
     */
    protected $queryData;

    /**
     * Contructors
     * @param array $queryData
     */
    public function __construct($queryData)
    {
        $this->queryData = $queryData;
    }

    /**
     * Defined route binded by module
     */
    abstract public static function defineRoutes();
    
    /*
     * Callback exemple
     */
    /*
     * public function callback($parameters)
     */
}
