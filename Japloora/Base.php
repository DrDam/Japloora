<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Japloora;

/**
 * Description of Base
 *
 * @author drdam
 */
class Base
{

    //put your code here
    
    /**
     * Find all class extending $base classe
     * @return type
     */
    public static function getExtends($base)
    {

        $children = array();
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, 'Japloora\\' . $base . 'Base', true)) {
                $children[] = $class;
            }
        }
               
        return $children;
    }
        /**
     * Find all class implementing $interface
     * @return type
     */
    public static function getImplementation($interface)
    {
        $classes = [];
        foreach (get_declared_classes() as $className) {
            if (in_array($interface, class_implements($className))) {
                $classes[] = $className;
            }
        }
        return $classes;
    }
    
    public static function discoverClasses($type)
    {

        $roots = [JAPLOORA_DOC_ROOT. '/modules', __DIR__];

        foreach ($roots as $root) {
            $modules = scandir($root);
            $base = $root;
            foreach ($modules as $module) {
                if ($module == '.' || $module == '..') {
                    continue;
                }

                if (is_dir($base. '/' . $module . '/' . $type)) {
                    $folders = scandir($base. '/' . $module . '/' . $type);
                    foreach ($folders as $file) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                                  
                        if (strstr($file, $type)) {
                            require_once $base. '/' . $module . '/' . $type . '/' . $file;
                        }
                    }
                }
            }
        }
    }
}
