<?php

/*
 * 
 */

namespace Japloora;

use Symfony\Component\Yaml\Yaml;

/**
 * Simple Config Manager
 */
class Config
{
    /**
     * Config objet
     * 
     * @var array config datas 
     */
    private $conf;
    
    /**
     * Load a conf.yml file
     * 
     * @param string $config_name
     */
    public function __construct($config_name) {
        list($module, $config_filename) = explode('.', $config_name);
        
        $file_path = $this->getfilePath($module, $config_filename);
        
        if($file_path != NULL) {
            try {
                $this->conf = Yaml::parse(file_get_contents($file_path));
            } catch (ParseException $e) {
                printf("Unable to parse the YAML string: %s", $e->getMessage());
                exit;
            }
        }
    }
    
    /**
     * 
     * Get a single or structured data from config
     * 
     * @param string $var
     * @return array|string|null
     */
    public function get($var) {
        // If the data directly exist, we return it
        if(isset($this->conf[$var])) {
            return $this->conf[$var];
        }
        
        // if path_var contain ".", we seek a data in array
        $path_var = explode('.', $var);
        
        // work on data
        $data = $this->conf;
        
        // foreach path_element
        foreach($path_var as $path_elem) {
            //check if exist and dive
            if(isset($data[$path_elem])) {
                $data = $data[$path_elem];
            }
            else {
                // if a step aren't exist, quit
                return NULL;
            }
        }
        
        return $data;
    }
    
    /**
     * Search config file in project
     * 
     * @param string $module_name
     * @param string $config_filename
     * @return string path to file
     */
    private function getfilePath($module_name, $config_filename) {
        $roots = [JAPLOORA_DOC_ROOT. '/modules', __DIR__];

        foreach($roots as $root) {
            $modules = scandir($root);
            $base = $root;
            foreach ($modules as $module) {
                if ($module == '.' || $module == '..') {
                    continue;
                }
                if($module == $module_name) {
                    $path = $base. '/' . $module .  '/Config';

                    if(file_exists($path . '/' . $config_filename . '.conf.yml')) {
                        return $path . '/' . $config_filename . '.conf.yml';
                    }
                }
            }
        }
        return NULL;
    }
}
