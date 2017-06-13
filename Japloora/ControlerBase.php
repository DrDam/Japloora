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
     * Find all controlers
     * @return type
     */
    static function getChildren() {

        $children = array();
        foreach (get_declared_classes() as $class) {

            if (is_subclass_of($class, 'Japloora\ControlerBase', TRUE)) {
                $children[] = $class;
            }
        }
        return $children;
    }

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
