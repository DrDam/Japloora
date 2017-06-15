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
class Base {
    //put your code here
    
        /**
     * Find all controlers
     * @return type
     */
    static public function getImplementation($base) {

        $children = array();
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, 'Japloora\\' . $base . 'Base', TRUE)) {
                $children[] = $class;
            }
        }
               
        return $children;
    }
    
    static public function discoverClasses($type) {

          $roots = [JAPLOORA_DOC_ROOT. '/modules', __DIR__];

      foreach($roots as $root) {
        $modules = scandir($root );
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
                  
              if(strstr($file, $type)) {
                require_once $base. '/' . $module . '/' . $type . '/' . $file;
              }
            }
          }
        }
      }
}
}
