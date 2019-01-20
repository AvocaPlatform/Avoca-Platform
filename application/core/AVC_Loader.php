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

class AVC_Loader extends CI_Loader
{
    public function __construct()
    {
        parent::__construct();

        $this->_ci_model_paths = [
            CUSTOMPATH,
            APPPATH,
        ];
    }

    protected function _ci_autoloader()
    {
        if (file_exists(APPPATH . 'config/autoload.php')) {
            include(APPPATH . 'config/autoload.php');
        }

        if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/autoload.php')) {
            include(APPPATH . 'config/' . ENVIRONMENT . '/autoload.php');
        }

        if (!isset($autoload)) {
            return;
        }

        // Autoload packages
        if (isset($autoload['packages'])) {
            foreach ($autoload['packages'] as $package_path) {
                $this->add_package_path($package_path);
            }
        }

        // Load any custom config file
        $autoload['config'][] = 'avoca';
        $autoload['config'][] = 'app_list_strings';
        foreach ($autoload['config'] as $val) {
            $this->config($val);
        }

        // Autoload helpers and languages
        $autoload['helper'][] = 'avoca';
        foreach (array(
                     'helper',
                     'language'
                 ) as $type) {
            if (isset($autoload[$type]) && count($autoload[$type]) > 0) {
                $this->$type($autoload[$type]);
            }
        }

        // Autoload drivers
        if (isset($autoload['drivers'])) {
            $this->driver($autoload['drivers']);
        }

        // Load libraries
        if (isset($autoload['libraries']) && count($autoload['libraries']) > 0) {
            // Load the database driver.
            if (in_array('database', $autoload['libraries'])) {
                $this->database();
                $autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
            }

            // Load all other libraries
            $this->library($autoload['libraries']);
        }

        // Autoload models
        if (isset($autoload['model'])) {
            $this->model($autoload['model']);
        }
    }

    public function model($model, $name = '', $db_conn = FALSE)
    {
        if (empty($model)) {
            return $this;
        } elseif (is_array($model)) {
            foreach ($model as $key => $value) {
                is_int($key) ? $this->model($value, '', $db_conn) : $this->model($key, $value, $db_conn);
            }

            return $this;
        }

        // Is the model in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($model, '/')) !== FALSE) {
            $model_arr = explode('/', $model);
            $module = $model_arr[0];
            $model = $model_arr[1];
        } else {
            throw new RuntimeException('Unable to locate the model you have specified: ' . $model . '. Need load model: module/model_name');
        }

        if (empty($name)) {
            $name = $model;
        }

        if (in_array($name, $this->_ci_models, TRUE)) {
            return $this;
        }

        $CI =& get_instance();
        if (isset($CI->$name)) {
            throw new RuntimeException('The model name you are loading is the name of a resource that is already being used: ' . $name);
        }

        if ($db_conn !== FALSE && !class_exists('CI_DB', FALSE)) {
            if ($db_conn === TRUE) {
                $db_conn = '';
            }

            $this->database($db_conn, FALSE, TRUE);
        }

        // Note: All of the code under this condition used to be just:
        //
        //       load_class('Model', 'core');
        //
        //       However, load_class() instantiates classes
        //       to cache them for later use and that prevents
        //       MY_Model from being an abstract class and is
        //       sub-optimal otherwise anyway.
        if (!class_exists('CI_Model', FALSE)) {
            $app_path = APPPATH . 'core' . DIRECTORY_SEPARATOR;
            if (file_exists($app_path . 'Model.php')) {
                require_once($app_path . 'Model.php');
                if (!class_exists('CI_Model', FALSE)) {
                    throw new RuntimeException($app_path . "Model.php exists, but doesn't declare class CI_Model");
                }

                log_message('info', 'CI_Model class loaded');
            } elseif (!class_exists('CI_Model', FALSE)) {
                require_once(BASEPATH . 'core' . DIRECTORY_SEPARATOR . 'Model.php');
            }

            $class = 'AVC_Model';
            require_once($app_path . $class . '.php');
        }

        $model_custom = "\\Custom\\Modules\\$module\\Models\\$model";
        if (class_exists($model_custom)) {
            $model = $model_custom;
        } else {
            $model = "\\App\\Modules\\$module\\Models\\$model";
            if (!class_exists($model)) {
                throw new RuntimeException('Unable to locate the model you have specified: ' . $model);
            }
        }

        $this->_ci_models[] = $name;
        $model = new $model();
        $CI->$name = $model;
        log_message('info', 'Model "' . get_class($model) . '" initialized');
        return $this;
    }
}