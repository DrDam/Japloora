<?php

namespace Japloora;

require_once 'helper.inc';

use Symfony\Component\HttpFoundation\Request;
use Japloora\ControlerBase;
use Japloora\Base;
     
/**
 * Core Object
 */
class Japloora extends Base
{

    const ROUTE_PARAMETER_REQUIRED = 1;
    const ROUTE_PARAMETER_OPTIONA = 0;
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
    public function __construct(Request $request, $debug = false)
    {
        $this->query_datas = $this->getQueryDatas($request);
        $this->debug = $debug;

        // Autoload classes
        $this->discoverClasses('Controler');

        // prepare routing
        $this->findAllRoutes();
    }

    /**
     * Extract data from HttpFoundation Request
     * @param Request $request
     * @return array
     */
    private function getQueryDatas(Request $request)
    {

        return array(
            'Schema' => $request->getScheme(),
            'Method' => $request->getRealMethod(),
            'Path' => $request->getPathInfo(),
            'Query' => $request->query->all(),
        );
    }

    /**
     * Discover and reference all Routes
     */
    private function findAllRoutes()
    {

        $defined_controlers = $this->getImplementation('Controler');
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
    public function run()
    {

        if ($this->debug === true) {
            watchdog(serialize($this->query_datas), 'QUERY DATAS');
        }

        return $this->routing();
    }

    /**
     * Find & execute correct controler
     * @param string $path
     * @return mixed
     */
    private function routing($path = null)
    {
        $possible = array();
        $end = 999;
        $validated = null;
        $response = array();
        $parameters = array();

        if ($path == null) {
            $path = $this->query_datas['Path'];
        }

        if ($this->debug === true) {
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
                            $validated = ($this->validateRouting($route_data) == []);
                            $possible['route'] = $route_data;
                            $possible['class'] = $classname;
                            $end = $new_end;
                        }
                    }
                } else {
                    //  If Route have exact definition
                    if ($route_name == $path) {
                        $new_validated = ($this->validateRouting($route_data) == []);

                        // If They'r the firste correspondance or a more accurate
                        if ($validated == true || ($validated == false && $new_validated === true)) {
                            $possible['route'] = $route_data;
                            $possible['class'] = $classname;
                            $validated = $new_validated;
                            $end = -1;
                            // If they'r the perfect correspondance
                            if ($validated === true) {
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
            if ($this->debug === true) {
                watchdog($possible['class'] . '->' . $possible['route']['callback'], 'RUNABLE CALLBACK');
            }

            // If the best correspondance contain validation Error
            if ($validated === false) {
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

            // Add Original Path to parameters
            $parameters['path'] = $path;

            // Call the Callback
   
            $controler = new $possible['class']($this->query_datas);
            $format = (isset($possible['route']['format'])) ? $possible['route']['format'] : 'JSON';
            $callback = $possible['route']['callback'];
            $this->output($controler->$callback($parameters, $path), $format);
            return null;
        }

        // There is no rout, return 404
        core_404();
    }


  /**
   * @param $output_datas
   * @param $format
   */
    private function output($output_datas, $format)
    {
        if ($format == 'JSON') {
            $code = (isset($output_datas['code'])) ? $output_datas['code'] : 200;
            json_output($output_datas['datas'], $code);
        } else {
            print $output_datas;
        }
    }
    
    /**
     * Alert if method/schema are incorect
     * @param string $parameter
     * @param string $badvalue
     * @return array
     */
    private function routingError($parameter, $badvalue)
    {
        return array(
            'datas' =>
            [
                'Routing Error' => "The route you'll try accessing not support " . $badvalue . " " . $parameter . '.'
            ],
            'code' => 405
        );
    }

    /**
     * Validating Route
     * @param array $route_data
     * @return array
     */
    private function validateRouting($route_data)
    {
        if (!isset($route_data['strict']) || $route_data['strict'] === true) {
            if (isset($route_data['scheme'])) {
                if (!is_array($route_data['scheme'])) {
                    $schemes = array($route_data['scheme']);
                } else {
                    $schemes = $route_data['scheme'];
                }
                if (!in_array($this->query_datas['Schema'], $schemes)) {
                    return $this->routingError('Schema', $this->query_data['Schema']);
                }
            }

            if (isset($route_data['method'])) {
                if (!is_array($route_data['method'])) {
                    $methods = array($route_data['method']);
                } else {
                    $methods = $route_data['method'];
                }
                if (!in_array($this->query_datas['Method'], $methods)) {
                    return $this->routingError('Method', $this->query_data['Method']);
                }
            }
        }
        return [];
    }

    /**
     * Bind Query parameters for Callback
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    private function bindParameters($parameters = array())
    {
        $bindedParams = array();

        foreach ($parameters as $key => $value) {
            // Manage default_value
            if (is_numeric($key)) {
                $key = $value;
                $value = self::ROUTE_PARAMETER_REQUIRED;
            }

            if ($value === self::ROUTE_PARAMETER_REQUIRED && !isset($this->query_datas['Query'][$key])) {
                throw new \Exception('The route you\'ll try accessing need "' . $key . '" parameter.');
            }

            if (isset($this->query_datas['Query'][$key])) {
                $bindedParams['Query'][$key] = $this->query_datas['Query'][$key];
            } else {
                $bindedParams['Query'][$key] = null;
            }
        }
        return $bindedParams;
    }
}
