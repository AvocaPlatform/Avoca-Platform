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
    return write_file(APPPATH . 'logs' . DIRECTORY_SEPARATOR . $log_name . '.log', $str, 'a+');
}

function getAppListStrings($name = null)
{
    $app_list_strings = config_item('app_list_strings');

    if ($name) {
        if (!empty($app_list_strings[$name])) {
            return $app_list_strings[$name];
        }
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
 * @return string
 */
function avoca_static()
{
    return avoca_url('/themes/' . config_item('theme_folder'));
}

/**
 * manage page url
 *
 * @param string $uri
 * @param null $protocol
 * @return string
 */
function avoca_manage($uri = '', $protocol = NULL)
{
    $prefix = config_item('manage_prefix');

    if (substr($uri, 0, 1) == '/') {
        $uri = $prefix . $uri;
    } else {
        $uri = $prefix . '/' . $uri;
    }


    return avoca_url($uri, $protocol);
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

function write_array2file($file, $array)
{
    $file = APPPATH . $file;

    $template = file_get_contents(APPPATH . 'config/avoca/builders/file_header.avc');

    $array_str = var_export($array, true);
    $data = "<?php\n" . $template . "\n\nreturn " . $array_str . ";\n";

    return write_file($file, $data, 'w');
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
 * get value in object|array
 *
 * @param $record
 * @param $field
 * @return string
 */
function recordFVal($record, $field)
{
    $value = '';
    if (isset($record->$field)) {
        $value = $record->$field;
    } else if (isset($record[$field])) {
        $value = $record[$field];
    }

    return $value;
}

/**
 * format field in record
 *
 * @param $record
 * @param $field
 * @param string $option
 * @param string $default
 * @return mixed
 */
function recordVal($record, $field, $option = '', $default = '')
{
    $value = recordFVal($record, $field);

    if ($value == '') {
        return $default;
    }

    $fieldModel = new \Avoca\Libraries\AvocaField();
    return $fieldModel->format($value, $record, $option);
}

/**
 * update link, get data from record
 *
 * @param $uri
 * @param $record
 * @return string
 */
function recordLink($uri, $record)
{
    $id = recordFVal($record, 'id');
    $uri = str_replace('{ID}', $id, $uri);

    return avoca_manage($uri);
}

/**
 * get field label from viewdefs
 *
 * @param $field
 * @param array $option
 * @return mixed|string
 */
function fieldLabel($field, $option = [])
{
    if (!empty($option['label'])) {
        return $option['label'];
    }

    return ucfirst($field);
}

/**
 * generate form input from viewdefs
 *
 * @param $field
 * @param $value
 * @param array $option
 * @return string
 */
function avoca_form($field, $value, $option = [])
{
    $fieldModel = new \Avoca\Libraries\AvocaField();
    if (empty($option['class'])) {
        $option['class'] = 'form-control';
    } else {
        $option['class'] = 'form-control ' . $option['class'];
    }

    return $fieldModel->form($field, $value, $option);
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