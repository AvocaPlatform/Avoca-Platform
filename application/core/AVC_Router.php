<?php
/**
 * Created by AVOCA.IO
 * Website: http://avoca.io
 * User: Jacky
 * Email: hungtran@up5.vn | jacky@youaddon.com
 * Person: tdhungit@gmail.com
 * Skype: tdhungit
 * Git: https://github.com/tdhungit
 */

require dirname(__FILE__) . '/Modules.php';


class AVC_Router extends CI_Router
{
    private $located = 0;

    public $module;

    public function __construct(array $routing = NULL)
    {
        parent::__construct($routing);
    }

    protected function _set_request($segments = array())
    {
        $segments = $this->_validate_request($segments);

        if (empty($segments)) {
            $this->_set_default_controller();
            return;
        }

        if ($this->translate_uri_dashes === TRUE) {
            $segments[0] = str_replace('-', '_', $segments[0]);
            if (isset($segments[1])) {
                $segments[1] = str_replace('-', '_', $segments[1]);
            }
        }

        $segments = $this->locate($segments);

        // @TODO will show when complete HMVC
//        if ($this->located == -1) {
//            $this->_set_module_path($this->routes['404_override']);
//            return;
//        }

        if (empty($segments)) {
            $this->_set_default_controller();
            return;
        }

        $this->set_class($segments[0]);

        if (isset($segments[1])) {
            $this->set_method($segments[1]);
        } else {
            $segments[1] = 'index';
        }

        array_unshift($segments, NULL);
        unset($segments[0]);
        $this->uri->rsegments = $segments;
    }

    protected function _set_routing()
    {
        // Load the routes.php file. It would be great if we could
        // skip this for enable_query_strings = TRUE, but then
        // default_controller would be empty ...
        if (file_exists(APPPATH . 'config/routes.php')) {
            include(APPPATH . 'config/routes.php');
        }

        if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/routes.php')) {
            include(APPPATH . 'config/' . ENVIRONMENT . '/routes.php');
        }

        // API
        # Authenticate
        $route['api/v(:num)/auth'] = "api_ver$1/auth/index";
        $route['api/v(:num)/auth/(:any)'] = "api_ver$1/auth/$2";

        # GET --> list records
        # POST --> create record
        # example: /api/v1/users --> api_ver1/Users/records
        $route['api/v(:num)/(:any)'] = "api_ver$1/$2/records";

        # GET --> detail record
        # PUT --> edit record
        # DELETE --> delete record
        # example: /api/v1/users/1 --> api_ver1/Users/record
        $route['api/v(:num)/(:any)/(:num)'] = "api_ver$1/$2/record/$3";

        // Controllers
        $route['api/v(:num)/(:any)/(:any)'] = "api_ver$1/$2/$3";
        $route['api/v(:num)/(:any)/(:any)/(.+)'] = "api_ver$1/$2/$3/$4";
        ##################################################

        // Validate & get reserved routes
        if (isset($route) && is_array($route)) {
            isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
            isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
            unset($route['default_controller'], $route['translate_uri_dashes']);
            $this->routes = $route;
        }

        // Are query strings enabled in the config file? Normally CI doesn't utilize query strings
        // since URI segments are more search-engine friendly, but they can optionally be used.
        // If this feature is enabled, we will gather the directory/class/method a little differently
        if ($this->enable_query_strings) {
            // If the directory is set at this time, it means an override exists, so skip the checks
            if (!isset($this->directory)) {
                $_d = $this->config->item('directory_trigger');
                $_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';

                if ($_d !== '') {
                    $this->uri->filter_uri($_d);
                    $this->set_directory($_d);
                }
            }

            $_c = trim($this->config->item('controller_trigger'));
            if (!empty($_GET[$_c])) {
                $this->uri->filter_uri($_GET[$_c]);
                $this->set_class($_GET[$_c]);

                $_f = trim($this->config->item('function_trigger'));
                if (!empty($_GET[$_f])) {
                    $this->uri->filter_uri($_GET[$_f]);
                    $this->set_method($_GET[$_f]);
                }

                $this->uri->rsegments = array(
                    1 => $this->class,
                    2 => $this->method
                );
            } else {
                $this->_set_default_controller();
            }

            // Routing rules don't apply to query strings and we don't need to detect
            // directories, so we're done here
            return;
        }

        // Is there anything to parse?
        if ($this->uri->uri_string !== '') {
            $this->_parse_routes();
        } else {
            $this->_set_default_controller();
        }
    }

    protected function _set_module_path(&$_route)
    {
        if (!empty($_route)) {
            // Are module/controller/method segments being specified?
            $sgs = sscanf($_route, '%[^/]/%[^/]/%[^/]/%s', $module, $class, $method, $params);
            if ($sgs != 3) {
                $method = 'index';
            }

            if (!$class) {
                $class = $module;
            }

            // set the module/controller directory location if found
            if ($this->locate(array(
                $module,
                $class
            ))) {
                //reset to class/method
                switch ($sgs) {
                    case 1:
                        $_route = $module . '/' . $class . '/index';
                        break;
                    case 2:
                        $_route = ($this->located == 1) ? $module . '/' . $class : $class . '/index';
                        break;
                    case 3:
                        $_route = ($this->located == 1) ? $class . '/' . $method : $method . '/index';
                        break;
                    default:
                        break;
                }
            }
        }
    }

    protected function _set_default_controller()
    {
        if (empty($this->directory)) {
            /* set the default controller module path */
            $this->_set_module_path($this->default_controller);
        }

        if (empty($this->default_controller)) {
            show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
        }

        // Is the method being specified?
        if (sscanf($this->default_controller, '%[^/]/%[^/]/%s', $module, $class, $method) !== 3) {
            $method = 'index';
        }

        if (!file_exists(APPPATH . 'modules/' . $this->directory . ucfirst($class) . '.php')) {
            // This will trigger 404 later
            return;
        }

        $this->set_class($class);
        $this->set_method($method);

        // Assign routed segments, index starting from 1
        $this->uri->rsegments = array(
            1 => $class,
            2 => $method
        );

        log_message('debug', 'No URI present. Default controller set.');
    }

    protected function _validate_request($segments)
    {
        $c = count($segments);
        $directory_override = isset($this->directory);

        // Loop through our segments and return as soon as a controller
        // is found or when such a directory doesn't exist
        while ($c-- > 0) {
            $test = $this->directory
                . ucfirst($this->translate_uri_dashes === TRUE ? str_replace('-', '_', $segments[0]) : $segments[0]);

            if (!file_exists(APPPATH . 'controllers/' . $test . '.php')
                && $directory_override === FALSE
                && is_dir(APPPATH . 'controllers/' . $this->directory . $segments[0])
            ) {
                $this->set_directory(array_shift($segments), TRUE);
                continue;
            }

            return $segments;
        }

        // This means that all segments were actually directories
        return $segments;
    }

    public function set_class($class)
    {
        $this->class = str_replace(array(
            '/',
            '.'
        ), '', $class);
    }

    public function fetch_module()
    {
        return $this->module;
    }

    public function locate($segments)
    {
        /* use module route if available */
        if (isset($segments[0]) && $routes = Modules::parse_routes($segments[0], implode('/', $segments))) {
            $segments = $routes;
        }

        /* get the segments array elements */
        list($module, $controller) = array_pad($segments, 2, NULL);

        /* check modules */
        foreach (Modules::$locations as $location => $offset) {
            /* module exists? */
            if (is_dir($source = $location . $module . '/controllers/')) {
                $this->module = $module;
                $this->directory = $offset . $module . '/controllers/';

                if (is_file($source . ucfirst($controller) . EXT)) {
                    $this->located = 1;
                    return array_slice($segments, 1);
                }

                if (is_file($source . ucfirst($module) . EXT)) {
                    $this->located = 0;
                    return $segments;
                }
            }
        }

        $this->located = -1;
        return $segments;
    }
}