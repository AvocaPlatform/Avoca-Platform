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

class AVC_Router extends CI_Router
{
    public function __construct(array $routing = NULL)
    {
        parent::__construct($routing);
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

        // Validate & get reserved routes
        if (isset($route) && is_array($route)) {
            isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
            isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
            unset($route['default_controller'], $route['translate_uri_dashes']);

            // api version 1
            $route['api/v1/(:any)'] = "api_ver1/$1/index";
            $route['api/v1/(:any)/(:any)'] = "api_ver1/$1/index/$2";

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

    public function set_class($class)
    {
        $this->class = str_replace(array(
            '/',
            '.'
        ), '', $class);
    }
}