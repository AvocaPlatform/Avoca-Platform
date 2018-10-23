<?php
/**
 * Created by UP5 Tech & YouAddOn.
 * Website: https://up5.vn
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

function getSysConfig($str)
{
    $ci = &get_instance();
    return $ci->config->item($str);
}

function getAppListStrings($name = null)
{
    $app_list_strings = getSysConfig('app_list_strings');

    if ($name) {
        if (!empty($app_list_strings[$name])) {
            return $app_list_strings[$name];
        }
    }

    return [];
}

function avoca_url($uri = '', $protocol = NULL)
{
    return base_url($uri, $protocol);
}

function avoca_static()
{
    return avoca_url('/themes/' . config_item('theme_folder'));
}