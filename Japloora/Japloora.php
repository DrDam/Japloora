<?php

namespace Japloora;

require_once 'helper.inc';

use Symfony\Component\HttpFoundation\Request;
use Japloora\ControlerBase;

define('ROUTE_PARAMETER_REQUIRED', 1);
define('ROUTE_PARAMETER_OPTIONAL', 0);
define('JAPLOORA_DOC_ROOT', $_SERVER['DOCUMENT_ROOT'] );
        
/**
 * Core Object
 */
Class Japloora {

    /**
     * Datas extract from HttpFoundation Request
     * @var array 
     */
    private $query_data;

    /**
     * Array of all routes defined in application
     * @var array 
     */
    private $routes;

    /**
     * General Debug flag
     * @var boolean 
     */
    private $debug;

    /**
     * Constructor
     * @param Request $request
     */
    public function __construct(Request $request, $debug = FALSE) {
        $this->query_datas = $this->getQueryDatas($request);
        $this->debug = $debug;

        // Autoload classes
        $this->discoverClasses();

        // prepare routing
        $this->findAllRoutes();
    }

    /**
     * Extract data from HttpFoundation Request
     * @param Request $request
     * @return array
     */
    private function getQueryDatas(Request $request) {

        return array(
            'Shema' => $request->getScheme(),
            'Method' => $request->getRealMethod(),
            'Path' => $request->getPathInfo(),
            'Query' => $request->query->all(),
        );
    }

    /**
     * Class Autoloader
     */
    private function discoverClasses() {

        $modules = scandir(JAPLOORA_DOC_ROOT . '/modules');
        $base = JAPLOORA_DOC_ROOT . '/modules';
        foreach ($modules as $module) {
            if ($module == '.' || $module == '..') {
                continue;
            }
   
            if (is_dir($base. '/' . $module . '/Controlers')) {
                $folders = scandir(JAPLOORA_DOC_ROOT . '/modules'. '/' . $module . '/Controlers');
                foreach ($folders as $controllers) {
                    if ($controllers === '.' || $controllers === '..') {
                        continue;
                    }
                    
                    require_once JAPLOORA_DOC_ROOT . '/modules'. '/' . $module . '/Controlers/' . $controllers;
                }
            }
        }
    }

    /**
     * Discover and reference all Routes
     */
    private function findAllRoutes() {

        $defined_controlers = ControlerBase::getChildren();
        foreach ($defined_controlers as $classname) {
            $local_routes = $classname::defineRoutes();
            $routes = array();
            foreach ($local_routes as $route_name => $route) {
                $routes[] = $route;
            }
            $this->routes[$classname] = $routes;
        }
    }

    /**
     * Run Core-Backen 
     * @return mixed
     */
    public function run() {

        if ($this->debug === TRUE) {
            watchdog(serialize($this->query_datas), 'QUERY DATAS');
        }

        return $this->routing();
    }

    /**
     * Find & execute correct controler 
     * @param string $path
     * @return mixed
     */
    private function routing($path = NULL) {
        $possible = array();
        $end = 999;
        $validated = NULL;
        $response = array();
        $parameters = array();

        if ($path == NULL) {
            $path = $this->query_datas['Path'];
        }

        if ($this->debug === TRUE) {
            watchdog($path, 'ROOTED PATH');
        }

        foreach ($this->routes as $classname => $routes) {

            foreach ($routes as $route_data) {
                $route_name = $route_data['path'];
                if (substr($route_name, 0, 1) != '/') {
                    $route_name = '/' . $route_name;
                }

                // If Route contaning wild card,
                if (substr($route_name, -1, 1) == '*') {

                    $fragment = substr($route_name, 0, strlen($route_name) - 1);
                    if (strstr($path, $fragment)) {

                        $new_end = strlen($path) - strlen($fragment);
                        // Find more exactly path
                        if ($new_end < $end) {
                            $validated = ($this->validateRouting($route_data) == '');
                            $possible['route'] = $route_data;
                            $possible['class'] = $classname;
                            $end = $new_end;
                        }
                    }
                } else {
                    //  If Route have exact definition
                    if ($route_name == $path) {
                                    
                        $new_validated = ($this->validateRouting($route_data) == '');

                        // If They'r the firste correspondance or a more accurate
                        if ($validated == NULL || ($validated == FALSE && $new_validated === TRUE)) {
                            $possible['route'] = $route_data;
                            $possible['class'] = $classname;
                            $validated = $new_validated;
                            $end = -1;
                            // If they'r the perfect correspondance
                            if ($validated === TRUE) {
                                break;
                            }
                        }
                    }
                }
            }
        }

        // If there a match, use the most accurate route
        if ($possible != array() && $end != 999) {

            // Debug
            if ($this->debug === TRUE) {
                watchdog($possible['class'] . '->' . $possible['route']['callback'], 'RUNABLE CALLBACK');
            }

            // If the best correspondance contain validation Error
            if ($validated === FALSE) {
                $this->output($this->validateRouting($possible['route']), 'JSON');
            }

            // If route define parameters, they are bind
            if (isset($possible['route']['parameters'])) {
                try {
                    $parameters = $this->bindParameters($possible['route']['parameters']);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            // Call the Callback
            $Controler = new $possible['class']($this->query_datas);
            $format = (isset($possible['route']['format'])) ? $possible['route']['format'] : 'JSON';
            $this->output($Controler->{$possible['route']['callback']}($parameters), $format);
        }

        // There is no rout, return 404
        core_404();
    }

    private function output($output_datas, $format) {
        if($format == 'JSON') {
            $code = (isset($output_datas['code'])) ? $output_datas['code'] : 200;
            json_output($output_datas['datas'], $code);
        }
        else {
            print $output_datas['datas'];
        }
    }
    
    /**
     * Alert if method/shema are incorect
     * @param string $parameter
     * @param string $badvalue
     * @return string
     */
    private function routingError($parameter, $badvalue) {
        return array(
            'datas' => [ 'Routing Error' => "The route you'll try accessing not support " . $badvalue . " " . $parameter . '.'],
            'code' => 405
        );
    }

    /**
     * Validating Route
     * @param array $route_data
     * @return string
     */
    private function validateRouting($route_data) {
        if (!isset($route_data['strict']) || $route_data['strict'] === TRUE) {

            if (isset($route_data['sheme']) && $route_data['sheme'] != $this->query_datas['Shema']) {
                return $this->routingError('Shema', $this->query_data['Shema']);
            }

            if (isset($route_data['method']) && strtoupper($route_data['method']) != strtoupper($this->query_datas['Method'])) {
                return $this->routingError('Method', $this->query_datas['Method']);
            }
        }
        return '';
    }

    /**
     * Bind Query parameters for Callback
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    private function bindParameters($parameters = array()) {
        $bindedParams = array();

        foreach ($parameters as $key => $value) {
            // Manage default_value
            if (is_numeric($key)) {
                $key = $value;
                $value = ROUTE_PARAMETER_REQUIRED;
            }

            if ($value === ROUTE_PARAMETER_REQUIRED && !isset($this->query_datas['Query'][$key])) {
                throw new \Exception('The route you\'ll try accessing need "' . $key . '" parameter.');
            }

            if (isset($this->query_datas['Query'][$key])) {
                $bindedParams[$key] = $this->query_datas['Query'][$key];
            } else {
                $bindedParams[$key] = NULL;
            }
        }
        return $bindedParams;
    }

}
