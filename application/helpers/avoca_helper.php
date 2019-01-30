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

function avoca_debug($var, $is_die = false)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';

    if ($is_die) {
        die();
    }
}

function avoca_log($data, $log_name = 'debug')
{
    if (is_string($data)) {
        $message = $data;
    } else {
        $message = json_encode($data);
    }

    $str = "\n" . date('Y-m-d H:i:s') . ": " . $message;
    return write_file(APPPATH . '../logs' . DIRECTORY_SEPARATOR . $log_name . '.log', $str, 'a+');
}

function avoca_theme()
{
    $CI = &get_instance();

    $module = $CI->router->fetch_module();
    if ($module == 'Admin') {
        return 'avoca';
    }

    return config_item('view_folder');
}

function getAppListStrings($name = null, $all = false)
{
    $app_list_strings = config_item('app_list_strings');

    if ($name) {
        if (!empty($app_list_strings[$name])) {
            return $app_list_strings[$name];
        }
    } else if ($all === true) {
        return $app_list_strings;
    }

    return [];
}

/**
 * base url
 *
 * @param string $uri
 * @param null $protocol
 * @return string
 */
function avoca_url($uri = '', $protocol = NULL)
{
    return base_url($uri, $protocol);
}

/**
 * static url
 *
 * @param bool $include_theme
 * @return string
 */
function avoca_static($include_theme = true)
{
    if ($include_theme) {
        $uri = '/themes/' . avoca_theme();
    } else {
        $uri = '/';
    }

    $public_folder = config_item('public_folder');
    if ($public_folder) {
        $uri = '/' . $public_folder . $uri;
    }

    return trim(avoca_url($uri), '/');
}

/**
 * admin page url
 *
 * @param string $uri
 * @param null $protocol
 * @return string
 */
function avoca_admin($uri = '', $protocol = NULL)
{
    $prefix = config_item('admin_prefix');

    if (substr($uri, 0, 1) == '/') {
        $uri = $prefix . $uri;
    } else {
        $uri = $prefix . '/' . $uri;
    }

    return avoca_url($uri, $protocol);
}

function avoca_currentUrl()
{
    $CI =& get_instance();
    $url = $CI->config->site_url($CI->uri->uri_string());
    return $_SERVER['QUERY_STRING'] ? $url . '?' . $_SERVER['QUERY_STRING'] : $url;
}

/**
 * @param $file
 * @param $array
 * @param int $folder 1:custom, 2:application
 * @return bool
 */
function write_array2file($file, $array, $folder = 1)
{
    if ($folder === 1) {
        $file = CUSTOMPATH . $file;
    } else if ($folder === 2) {
        $file = APPPATH . $file;
    }

    $template = file_get_contents(APPPATH . 'modules/Admin/Config/builders/file_header.avc');
    $array_str = var_export($array, true);
    $data = "<?php\n" . $template . "\n\nreturn " . $array_str . ";\n";
    return write_file($file, $data, 'w');
}

function get_file_array($file, $default = [])
{
    if (file_exists($file)) {
        return include $file;
    }

    if (file_exists(CUSTOMPATH . $file)) {
        return include CUSTOMPATH . $file;
    }

    if (file_exists(APPPATH . $file)) {
        return include APPPATH . $file;
    }

    return $default;
}

/**
 * get session flash message
 *
 * @param $type
 * @return string
 */
function get_flash($type)
{
    $ci = &get_instance();
    $message = $ci->session->flashdata($type);

    if ($message) {
        return htmlentities($message);
    }

    return '';
}

/**
 * translate
 *
 * @param $str
 * @return mixed
 */
function __($str)
{
    $ci =& get_instance();
    return $ci->lang->line($str);
}

/**
 * generate form input from viewdefs
 *
 * @param $field
 * @param $record
 * @param array $option
 * @return string
 */
function avoca_form($field, $record, $option = [])
{
    return \Avoca\AvocaField::form($field, $record, $option);
}

/**
 * $_GET
 *
 * @param $name
 * @return string
 */
function avoca_GET($name)
{
    if ($name) {
        $CI =& get_instance();
        return $CI->input->get($name);
    }

    return '';
}