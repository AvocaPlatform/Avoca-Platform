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


class AVC_Config extends CI_Config
{
    public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE, $_module = '')
    {
        if (in_array($file, $this->is_loaded, TRUE)) {
            return $this->item($file);
        }

        $CI = &get_instance();
        $_module OR $_module = $CI->router->fetch_module();
        list($path, $file) = Modules::find($file, $_module, 'config/');

        if ($path === FALSE) {
            parent::load($file, $use_sections, $fail_gracefully);
            return $this->item($file);
        }

        if ($config = Modules::load_file($file, $path, 'config')) {
            /* reference to the config array */
            $current_config =& $this->config;

            if ($use_sections === TRUE) {
                if (isset($current_config[$file])) {
                    $current_config[$file] = array_merge($current_config[$file], $config);
                } else {
                    $current_config[$file] = $config;
                }

            } else {
                $current_config = array_merge($current_config, $config);
            }

            $this->is_loaded[] = $file;
            unset($config);
            return $this->item($file);
        }
    }
}