<?php

/**
 * Japloora Core Class
 */

namespace Japloora;

// Application need root folder
define('JAPLOORA_DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('ROUTE_PARAMETER_REQUIRED', 1);
define('ROUTE_PARAMETER_OPTIONAL', 0);
define('ROUTE_PARAMETER_TYPE_STRING', 'string');
define('ROUTE_PARAMETER_TYPE_INT', 'int');
define('ROUTE_PARAMETER_TYPE_NUM', 'numeric');
define('ROUTE_PARAMETER_TYPE_BOOL', 'bool');
define('ROUTE_PARAMETER_TYPE_ARRAY', 'array');
define('ROUTE_PARAMETER_SCHEME_HTTP', 'http');
define('ROUTE_PARAMETER_SCHEME_HTTPS', 'https');
define('ROUTE_PARAMETER_METHOD_GET', 'GET');
define('ROUTE_PARAMETER_METHOD_POST', 'POST');
define('ROUTE_PARAMETER_METHOD_PATCH', 'PATCH');
define('ROUTE_PARAMETER_METHOD_PUT', 'PUT');
define('ROUTE_PARAMETER_METHOD_DELETE', 'DELETE');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Japloora\Authent\AuthentManager;
use Japloora\Authent\AuthentAccessLog;
use Japloora\Watchdog;
use Japloora\JSONOutput;
use Japloora\Base;

/**
 * Core Object
 */
class Japloora extends Base
{

    /**
     * Datas extract from HttpFoundation Request
     * @var array
     */
    private $queryData;

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
     * Master Logger
     * @var Japloora\Authent\AuthentAccessLog
     */
    private $logger;
    
    /**
     * Constructor
     * @param Request $request
     */
    public function __construct(Request $request, $debug = false)
    {
        // Create logger
        $this->logger = new AuthentAccessLog();
        
        if (file_exists(JAPLOORA_DOC_ROOT . '/init/init.yml')) {
            $this->initialization();
            // Delete intialization file after first runing
            unlink(JAPLOORA_DOC_ROOT . '/init/init.yml');
        }
        $this->queryDatas = $this->getQueryDatas($request);
        $this->debug = $debug;

        // Autoload Controllers
        $this->discoverClasses('Controller');

        // prepare routing
        $this->findAllRoutes();
    }

    /**
     * Initialize Application
     * @param type $conf
     */
    private function initialization()
    {
        try {
            $conf = Yaml::parse(file_get_contents(JAPLOORA_DOC_ROOT . '/init/init.yml'));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
            exit;
        }

        // Find Init Classes
        $this->discoverClasses('Init');
        $initialisers = $this->getExtends('Init');

        // Run All Initializers
        foreach ($initialisers as $initialiser) {
            $initialiser::initialize($conf);
        }
    }

    /**
     * Extract data from HttpFoundation Request
     * @param Request $request
     * @return array
     */
    private function getQueryDatas(Request $request)
    {
        return array(
            'Headers' => $request->headers->all(),
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
        $defined_controllers = $this->getExtends('Controller');
        foreach ($defined_controllers as $classname) {
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
            Watchdog::write(serialize($this->queryDatas), 'QUERY DATAS');
        }
        return $this->routing();
    }

    /**
     * Find & execute correct controller
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
            $path = $this->queryDatas['Path'];
        }

        if ($this->debug === true) {
            Watchdog::write($path, 'ROUTED PATH');
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
                            if ($validated === false) {
                                continue;
                            }
                            $possible['route'] = $route_data;
                            $possible['class'] = $classname;
                            $end = $new_end;
                        }
                    }
                } else {
                    //  If Route have exact definition
                    if ($route_name == $path) {
                        $new_validated = ($this->validateRouting($route_data) == []);

                        if ($new_validated === false) {
                            continue;
                        }
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
                Watchdog::write($possible['class'] . '->' . $possible['route']['callback'], 'RUNABLE CALLBACK');
            }

            // If the best correspondance contain validation Error
            if ($validated === false) {
                JSONOutput::end($this->validateRouting($possible['route']));
            }

            // If route define parameters, they are bind
            if (isset($possible['route']['parameters'])) {
                try {
                    $parameters = $this->bindParameters($possible['route']['parameters']);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            if ($end != -1) {
                $queryFragments = [];
                $target_path = explode('/', $possible['route']['path']);
                $url_elems = explode('/', $path);
                // output first '/' on $path
                array_shift($url_elems);
                foreach ($target_path as $key => $fragment) {
                    if ($fragment == '*' && $url_elems[$key] != '') {
                        $queryFragments[] = $url_elems[$key];
                    }
                }
                $parameters['queryFragments'] = $queryFragments;
            }
            // Add Original Path to parameters
            $parameters['path'] = $path;
            $is_authent = false;
            // If need Authent
            if (isset($possible['route']['authent'])
                    && isset($possible['route']['authent']['permission'])
                    && $possible['route']['authent']['permission'] != ''
            ) {
                $is_authent = true;
                $headers = $this->queryDatas['Headers'];
                $auth_head = $headers['authorization'];
                if($auth_head == NULL) {
                    JSONOutput::send403();
                }
                // expected form : authorization : Token XXXXXXXXXXXXXXXXX
                $token_value = explode(' ', $auth_head[0])[1];
                $db = AuthentManager::connexion();
                $validation = $db->checkToken($token_value);
                if (isset($possible['route']['authent']['permission'])) {
                    if ($db->userAccess(
                        $validation['user_id'],
                        $possible['route']['authent']['permission']
                    )
                                === false) {
                        $this->logger->log($validation['user_id'], $this->queryDatas['Method'], $path, 403);
                        JSONOutput::send403();
                    }
                }

                if ($validation['status'] === false) {
                    JSONOutput::send403($validation['message']);
                } else {
                    // Add User_id to parameters
                    $parameters['user_id'] = $validation['user_id'];
                }
            }

            // Call the Callback
            $controller = new $possible['class']($this->queryDatas);
            $callback = $possible['route']['callback'];

            $controller->setParameters($parameters);
            $output_datas = $controller->$callback();
            $code = (isset($output_datas['code'])) ? $output_datas['code'] : 200;
            $output = (isset($output_datas['datas'])) ? $output_datas['datas'] : [];
            // log Datas
            $user_id = (isset($parameters['user_id'])) ? $parameters['user_id'] : null;

            if ($user_id === null) {
                if (isset($this->queryDatas['Query']['Login'])) {
                    $user_id = $this->queryDatas['Query']['Login'];
                }
            }
            $this->logger->log($user_id, $this->queryDatas['Method'], $path, $code);

            JSONOutput::end($output, $code);
        }

        // There is no rout, return 404
        $this->logger->log('', $this->queryDatas['Method'], $path, 404);
        JSONOutput::send404();
    }

    /**
     * Alert if method/schema are incorect
     * @param string $parameter
     * @param string $badvalue
     * @return array
     */
    private function routingError($parameter, $badvalue)
    {
        $data = array(
            'datas' =>
            [
                'Routing Error' => "The route you'll try accessing not support " . $badvalue . " " . $parameter . '.',
                'Code' => 405,
            ],
        );
        return $data;
    }

    /**
     * Validating Route
     * @param array $route_data
     * @return array
     */
    private function validateRouting($route_data)
    {

        if (!$this->routingItemValidator(
            'scheme',
            $this->queryDatas['Schema'],
            ROUTE_PARAMETER_SCHEME_HTTP,
            $route_data
        )) {
            return $this->routingError('Schema', $this->queryData['Schema']);
        }

        if (!$this->routingItemValidator(
            'method',
            $this->queryDatas['Method'],
            ROUTE_PARAMETER_METHOD_GET,
            $route_data
        )) {
            return $this->routingError('Method', $this->queryData['Method']);
        }
        return [];
    }

    private function routingItemValidator($value, $target, $default, $route_data)
    {
        $item = (isset($route_data[$value])) ? $route_data[$value] : [$default];
        if (!is_array($item)) {
            $item = [$item];
        }
        if (!in_array($target, $item)) {
            return false;
        }
        return true;
    }

    /**
     * Bind Query parameters for Callback
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    private function bindParameters($parameters = array())
    {
        //print_r($parameters);
        $bindedParams = array();

        foreach ($parameters as $key => $parameter_confs) {
            $mandatory = (isset($parameter_confs['mandatory']))
                    ? $parameter_confs['mandatory']
                    : ROUTE_PARAMETER_REQUIRED;
            $type = (isset($parameter_confs['type'])) ? $parameter_confs['type'] : ROUTE_PARAMETER_TYPE_STRING;

            if ($mandatory === ROUTE_PARAMETER_REQUIRED && !isset($this->queryDatas['Query'][$key])) {
                throw new \Exception('The route you\'ll try accessing need "' . $key . '" parameter.');
            }

            if (isset($this->queryDatas['Query'][$key])) {
                $parameter = $this->queryDatas['Query'][$key];
                if ($type == ROUTE_PARAMETER_TYPE_ARRAY && !is_array($parameter)) {
                    throw new \Exception('The paramater ' . $key . ' need array data.');
                }
                if ($type == ROUTE_PARAMETER_TYPE_INT && !is_int($parameter + 0)) {
                    throw new \Exception('The paramater ' . $key . ' need integer data.');
                }
                if ($type == ROUTE_PARAMETER_TYPE_BOOL && !is_bool($parameter)) {
                    throw new \Exception('The paramater ' . $key . ' need boolean data.');
                }
                if ($type == ROUTE_PARAMETER_TYPE_NUM & !is_numeric($parameter)) {
                     throw new \Exception('The paramater ' . $key . ' need numeric data.');
                }
                $bindedParams['Query'][$key] = $this->queryDatas['Query'][$key];
            } else {
                $bindedParams['Query'][$key] = null;
            }
        }
        return $bindedParams;
    }
}
