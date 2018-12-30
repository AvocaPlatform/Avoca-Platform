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


class AVC_Lang extends CI_Lang
{
    public $base_language = 'english';

    public function __construct()
    {
        parent::__construct();
    }

    public function load($langfile, $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
    {
        if (is_array($langfile)) {
            foreach ($langfile as $value) {
                $this->load($value, $idiom, $return, $add_suffix, $alt_path);
            }

            return;
        }

        $langfile = str_replace('.php', '', $langfile);
        if ($add_suffix === TRUE) {
            $langfile = preg_replace('/_lang$/', '', $langfile) . '_lang';
        }

        $langfile .= '.php';
        if (empty($idiom) OR !preg_match('/^[a-z_-]+$/i', $idiom)) {
            $config = &get_config();
            $idiom = empty($config['language']) ? $this->base_language : $config['language'];
        }

        if ($return === FALSE && isset($this->is_loaded[$langfile]) && $this->is_loaded[$langfile] === $idiom) {
            return;
        }

        $found = FALSE;

        // Do we have an alternative path to look in?
        if ($alt_path !== '') {
            if (file_exists($alt_path)) {
                include($alt_path);
                $found = TRUE;
            }
        } else {
            $package_path = APPPATH . 'language/' . $idiom . '/' . $langfile;
            if (file_exists($package_path)) {
                include($package_path);
                $found = TRUE;
            }
        }

        if ($found !== TRUE) {
            //show_error('Unable to load the requested language file: language/' . $idiom . '/' . $langfile);
        }

        if (!isset($lang) OR !is_array($lang)) {

            log_message('error', 'Language file contains no data: language/' . $idiom . '/' . $langfile);
            if ($return === TRUE) {
                return array();
            }

            return;
        }

        if ($return === TRUE) {
            return $lang;
        }

        $this->is_loaded[$langfile] = $idiom;
        $this->language = array_merge($this->language, $lang);

        log_message('info', 'Language file loaded: language/' . $idiom . '/' . $langfile);
        return TRUE;
    }

    public function line($line, $log_errors = TRUE)
    {
        if (!$line) {
            return '';
        }

        $value = isset($this->language[$line]) ? $this->language[$line] : $line;

        return $value;
    }
}