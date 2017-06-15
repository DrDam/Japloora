<?php

namespace Japloora;

/**
 * Abstract class for all controler
 */
Abstract Class ControlerBase {

    /**
     * Datas extract from HttpFoundation Request
     * @var array 
     */
    protected $queryData;

    /**
     * Contructors
     * @param array $queryData
     */
    public function __construct($queryData) {
        $this->queryData = $queryData;
    }

    /**
     * Defined route binded by module
     */
    abstract static function defineRoutes();
    
    /*
     * Callback exemple
     */
    /*
     * public function callback($parameters)
     */
}
