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

function write_array2file($file, $array)
{
    $file = APPPATH . $file;

    $template = file_get_contents(APPPATH . 'avoca/builders/file_header.avc');

    $array_str = var_export($array, true);
    $data = "<?php\n" . $template . "\n\nreturn " . $array_str . ";\n";

    return write_file($file, $data, 'w');
}