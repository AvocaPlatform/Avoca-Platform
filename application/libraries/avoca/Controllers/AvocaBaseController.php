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


namespace Avoca\Libraries\Controllers;

/**
 * Class AvocaBaseController
 * @package Avoca\Libraries\Controllers
 *
 * @property \AVC_Router $router
 * @property \AVC_Loader $load
 * @property \CI_DB_query_builder $db
 * @property \AVC_Input $input
 * @property \CI_Session $session
 * @property \CI_DB_forge $dbforge
 * @property \AVC_URI $uri
 * @property \AVC_Lang $lang
 */
class AvocaBaseController extends \CI_Controller
{
    protected $version = '1.0';

    protected $controller_name;
    protected $action_name;

    protected $language = 'english';
    protected $lang_files = [];

    protected $httpCode = 200;
    protected $httpCodeText = 'Ok';

    protected $errors = [];

    public function __construct()
    {
        parent::__construct();

        $this->controller_name = $this->router->fetch_class();
        $this->action_name = $this->router->fetch_method();

        // load language
        $this->loadLanguage();

        // init
        $this->init();

        // check authenticate
        if ($this->authenticate() === false) {
            $this->authenticateError();
            die();
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
        $this->lang->load('app_strings', $this->language);
        $this->lang->load($this->controller_name, $this->language);

        foreach ($this->lang_files as $lang) {
            $this->lang->load($lang, $this->language);
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
        $this->redirect('/login');
    }

    protected function isLogin()
    {
        $user_id = $this->session->userdata('user_id');
        if ($user_id) {
            return true;
        }

        return false;
    }

    /**
     * detect request method (POST, PUT, GET, DELETE)
     * @return string
     */
    protected function detectMethod() {
        $method = strtolower($this->input->server('REQUEST_METHOD'));

        if (config_item('enable_emulate_request')) {
            if ($this->input->post('_method')) {
                $method = strtolower($this->input->post('_method'));
            } else if ($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $method = strtolower($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            }
        }

        if (in_array($method, array('get', 'delete', 'post', 'put'))) {
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
        $value = $this->input->post($name);

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
     * redirect to manage page
     *
     * @param $uri
     * @param string $method
     * @param null $code
     * @return bool
     */
    protected function manage_redirect($uri, $method = 'auto', $code = null)
    {
        return $this->redirect(avoca_manage($uri), $method, $code);
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
}
