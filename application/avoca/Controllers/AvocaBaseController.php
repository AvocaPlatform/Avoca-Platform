<?php
/**
 * Created by Jacky.
 * Developer
 * Email: jacky@youaddon.com / hungtran@up5.vn
 * Phone: +84 972014011
 * Skype: tdhungit
 * Site: https://youaddon.com / https://up5.vn
 * Github: https://github.com/teamcarodev / https://github.com/youaddon
 * Facebook: https://www.facebook.com/jackytran0101
 */


namespace Avoca\Controllers;


/**
 * Class AvocaBaseController
 * @package Avoca\Controllers
 *
 * @property \AVC_Router $router
 * @property \AVC_Loader $load
 * @property \CI_DB_query_builder $db
 * @property \AVC_Input $input
 * @property \CI_Session $session
 * @property \CI_DB_forge $dbforge
 * @property \AVC_URI $uri
 * @property \AVC_Lang $lang
 * @property \CI_Pagination $pagination
 */
class AvocaBaseController extends \CI_Controller
{
    protected $version = '1.1';

    public $autoload = array();

    protected $module_name;
    protected $controller_name;
    protected $action_name;

    protected $language = 'english';
    protected $lang_files = [];

    protected $httpCode = 200;
    protected $httpCodeText = 'Ok';

    protected $require_auth = false;

    protected $is_error = false;
    protected $errors = [];

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

        parent::__construct();

        $this->module_name = $this->router->fetch_module();
        $this->controller_name = $this->router->fetch_class();
        $this->action_name = $this->router->fetch_method();

        // check request api
        if ($this->detectMethod() == 'options') {
            // exit return code 200
            exit(200);
        }

        // load language
        $this->loadLanguage();

        // init
        $this->init();

        // check authenticate
        if ($this->require_auth) {
            if ($this->authenticate() === false) {
                $this->is_error = true;
                $this->authenticateError();
                die();
            }
        }
    }

    /**
     * init controller class
     */
    protected function init()
    {

    }

    /**
     * load language file
     */
    protected function loadLanguage()
    {
        // load app strings
        $this->lang->load('app_strings', $this->language);

        // load module strings
        $package_path = 'modules/' . $this->module_name . '/language/' . $this->module_name . '_' . $this->language . '.php';
        $this->lang->load($this->module_name, $this->language, false, true, APPPATH . $package_path);
        $this->lang->load($this->module_name, $this->language, false, true, CUSTOMPATH . $package_path);

        // load custom strings
        foreach ($this->lang_files as $lang) {
            if ($lang != $this->module_name) {
                $package_path = 'modules/' . $this->module_name . '/language/' . $lang . '_' . $this->language . '.php';
                $this->lang->load($lang, $this->language, false, true, APPPATH . $package_path);
                $this->lang->load($lang, $this->language, false, true, CUSTOMPATH . $package_path);
            }
        }
    }

    /**
     * check authenticate
     *
     * @return bool
     */
    protected function authenticate()
    {
        return true;
    }

    /**
     * if authenticate error
     */
    protected function authenticateError()
    {
        $this->redirect('/auth');
    }

    /**
     * check login or not by session
     *
     * @return bool
     */
    protected function isLogin()
    {
        $user_id = $this->getSession('user_id');

        if ($user_id) {
            return true;
        }

        return false;
    }

    /**
     * detect request method (POST, PUT, GET, DELETE)
     * @return string
     */
    protected function detectMethod()
    {
        $method = strtolower($this->input->server('REQUEST_METHOD'));

        if (config_item('enable_emulate_request')) {
            if ($this->input->post('_method')) {
                $method = strtolower($this->input->post('_method'));
            } else if ($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $method = strtolower($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            }
        }

        if (in_array($method, array(
            'get',
            'delete',
            'post',
            'put',
            'options'
        ))) {
            return $method;
        }

        return 'get';
    }

    /**
     * check request method is post
     *
     * @return bool
     */
    protected function isPost()
    {
        if ($this->detectMethod() == 'post') {
            return true;
        }

        return false;
    }

    /**
     * get POST
     *
     * @param null $name
     * @return mixed|string
     */
    protected function getPost($name = null)
    {
        $post = $this->input->post();
        if (empty($post)) {
            try {
                $post = json_decode(trim(file_get_contents('php://input')), true);
            } catch (\Exception $exception) {
                $post = [];
            }
        }

        $value = isset($post[$name]) ? $post[$name] : '';

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    /**
     * get GET
     *
     * @param null $name
     * @return mixed|string
     */
    protected function getQuery($name = null)
    {
        $value = $this->input->get($name);

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    /**
     * get post or get
     *
     * @param null $name
     * @return mixed|string
     */
    protected function getRequest($name = null)
    {
        $value = $this->input->post_get($name);

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    /**
     * redirect to uri or url
     *
     * @param $uri
     * @param string $method
     * @param null $code
     * @return bool
     */
    protected function redirect($uri, $method = 'auto', $code = null)
    {
        redirect($uri, $method, $code);
        return true;
    }

    /**
     * redirect to admin page
     *
     * @param $uri
     * @param string $method
     * @param null $code
     * @return bool
     */
    protected function admin_redirect($uri, $method = 'auto', $code = null)
    {
        return $this->redirect(avoca_admin($uri), $method, $code);
    }

    /**
     * set error message
     *
     * @param $messages
     */
    protected function setErrors($messages)
    {
        if (is_string($messages)) {
            $this->errors[] = $messages;
        } else {
            $this->errors = array_merge($this->errors, $messages);
        }
    }

    /**
     * @param $modelName
     * @return \AVC_Model
     */
    protected function getModel($modelName)
    {
        if (strpos($modelName, '/') === false) {
            $model = $modelName;
            $modelName = $this->module_name . '/' . $modelName;
        } else {
            $arr = explode('/', $modelName);
            $model = $arr[1];
        }

        $this->load->model($modelName);
        return $this->$model;
    }

    /**
     * set session from data [name => value]
     *
     * @param $data array
     */
    protected function setSession($data)
    {
        $this->session->set_userdata($data);
    }

    /**
     * get session data
     *
     * @param $name
     * @return mixed|null
     */
    protected function getSession($name)
    {
        if ($this->session->has_userdata($name)) {
            return $this->session->userdata($name);
        }

        return null;
    }
}
