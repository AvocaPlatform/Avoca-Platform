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

/**
 * Class AVC_Controller
 * @property AVC_Router $router
 * @property AVC_Loader $load
 * @property CI_DB_query_builder $db
 * @property AVC_Input $input
 * @property CI_Session $session
 * @property CI_DB_forge $dbforge
 * @property AVC_URI $uri
 */
class AVC_Controller extends CI_Controller
{
    use CiPug;

    protected $view_type = 'html';
    protected $view_path = '';
    protected $view_disable = false;

    protected $controller_name;
    protected $action_name;

    protected $options = [];
    protected $dataGlobal = [];
    protected $data = [];

    protected $css = [];
    protected $js = [];

    protected $_supported_formats = [
        'json' => 'application/json',
        'array' => 'application/json',
        'csv' => 'application/csv',
        'html' => 'text/html',
        'jsonp' => 'application/javascript',
        'php' => 'text/plain',
        'serialized' => 'application/vnd.php.serialized',
        'xml' => 'application/xml'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->init();
        $this->setViewType();
        $this->setViewFolderPath();
        $this->authenticate();
    }

    protected function init()
    {
        $this->controller_name = $this->router->fetch_class();
        $this->action_name = $this->router->fetch_method();

        $page_title = 'Avoca Framework';
        if (!empty($this->data['title'])) {
            $page_title = $this->data['title'] . ' | ' . $page_title;
        }

        $this->addGlobals([
            '_start_rtime' => microtime(true),
            '_controller' => $this->controller_name,
            '_action' => $this->action_name,
            '_pageTitle' => $page_title,
        ]);
    }

    protected function setViewType()
    {
        $format = $this->uri->format();
        if ($format) {
            $this->view_type = $format;
        }
    }

    protected function disableView()
    {
        $this->view_disable = true;
    }

    protected function setViewFolderPath()
    {
        try {
            $this->settings([
                'view_path' => VIEWPATH . $this->getViewFolder(),
                'cache' => APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'pug'
            ]);
        } catch (Exception $e) {
            show_error('ERROR view path: ' . VIEWPATH);
        }
    }

    protected function authenticate()
    {
        if (!$this->isLogin()) {
            // @TODO
        }
    }

    protected function isLogin() {
        $user_id = $this->session->userdata('user_id');
        if ($user_id) {
            return true;
        }

        return false;
    }

    /**
     * add variable global for view
     *
     * @param $name
     * @param $value
     */
    protected function addGlobal($name, $value)
    {
        $this->dataGlobal[$name] = $value;
    }

    /**
     * add multi global variable for view
     *
     * @param $data
     */
    protected function addGlobals($data)
    {
        $this->dataGlobal = array_merge($this->dataGlobal, $data);
        $this->data = array_merge($this->dataGlobal, $this->data);
    }

    /**
     * @param $modelName
     * @return AVC_Model
     */
    protected function getModel($modelName)
    {
        $this->load->model($modelName);
        return $this->$modelName;
    }

    protected function getViewFolder()
    {
        return config_item('view_folder');
    }

    /**
     * get option of options variable
     *
     * @param $name
     * @param string $default
     * @return mixed|string
     */
    protected function getOption($name, $default = '')
    {
        if (!empty($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    protected function jsonData()
    {
        header('Content-Type: application/json');
        echo json_encode($this->data);
        return true;
    }

    protected function isPost()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
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
     * render to view
     *
     * @param bool $return
     * @throws Exception
     */
    protected function display($return = false)
    {
        $this->autoGlobals();

        if (!$this->view_path) {
            $this->view_path = strtolower($this->controller_name) . DIRECTORY_SEPARATOR . strtolower($this->action_name);
        }

        $this->view($this->fixedViewPath(), $this->data, $return);
    }

    /**
     * check custom view from folder view/<>/custom
     *
     * @return string
     */
    protected function fixedViewPath()
    {
        $view_path = 'templates' . DIRECTORY_SEPARATOR . $this->view_path;
        $custom_path = 'custom' . DIRECTORY_SEPARATOR . $this->view_path;

        if (file_exists(VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $custom_path . '.pug')) {
            $view_path = $custom_path;
        }

        return $view_path;
    }

    /**
     * some global variable. auto set when render
     */
    protected function autoGlobals()
    {
        $this->dataGlobal['CSS'] = $this->getCss();
        $this->dataGlobal['JS'] = $this->getJs();
    }

    protected function setFlash($message, $type = 'info')
    {
        $this->session->set_flashdata($type, $message);
    }

    protected function setError($message)
    {
        $this->setFlash($message, 'error');
    }

    protected function setWarning($message)
    {
        $this->setFlash($message, 'warn');
    }

    protected function setSuccess($message)
    {
        $this->setFlash($message, 'success');
    }

    protected function addCss($css_files)
    {
        if (is_string($css_files)) {
            $this->css = [$css_files];
        } else {
            $this->css = $css_files;
        }
    }

    protected function addJs($js_files)
    {
        if (is_string($js_files)) {
            $this->js = [$js_files];
        } else {
            $this->js = $js_files;
        }
    }

    protected function getCss()
    {
        $link = [];

        foreach ($this->css as $css) {

            if (strpos($css, 'https') !== false || strpos($css, 'http') !== false) {
                $link[] = $css;
            } else {
                $link[] = base_url() . $css;
            }
        }

        return $link;
    }

    protected function getJs()
    {
        $src = [];

        foreach ($this->js as $js) {

            if (strpos($js, 'https') !== false || strpos($js, 'http') !== false) {
                $src[] = $js;
            } else {
                $src[] = base_url() . $js;
            }
        }

        return $src;
    }

    protected function redirect($uri, $method = 'auto', $code = null)
    {
        redirect($uri, $method, $code);
        return true;
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->addGlobals([
            '_end_rtime' => microtime(true),
        ]);

        if ($this->view_type == 'json') {
            $this->jsonData();
            return true;
        }

        if ($this->view_disable) {
            return true;
        }

        $this->display();
        return true;
    }
}

/**
 * Class AVC_AdminController
 */
class AVC_AdminController extends AVC_Controller
{
    protected $model = '';

    protected function authenticate()
    {

    }

    /**
     * @param bool $return
     * @throws Exception
     */
    protected function display($return = false)
    {
        $this->autoGlobals();

        if (!$this->view_path) {

            $root_path = 'templates' . DIRECTORY_SEPARATOR;
            $view = $this->router->directory . strtolower($this->controller_name) . DIRECTORY_SEPARATOR . strtolower($this->action_name);

            if (!file_exists(VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $root_path . $view . '.pug')) {

                $view = 'admin_templates' . DIRECTORY_SEPARATOR . strtolower($this->action_name);

                if (!file_exists(VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $root_path . $view . '.pug')) {
                    show_error('ERROR template: ' . VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $root_path . $view . '.pug');
                }
            }

            $this->view_path = $view;
        }

        $this->view($this->fixedViewPath(), $this->data, $return);
    }

    /**
     * @param string $modelName
     * @return AVC_Model
     */
    protected function getModel($modelName = '')
    {
        if (empty($modelName)) {
            $modelName = $this->model;
        }

        return parent::getModel($modelName);
    }

    // ACTION
    public function index()
    {
        // check create link. default controller/action
        $this->data['create_link'] = $this->getOption('create_link', $this->controller_name . '/edit');

        $this->data['records'] = $this->getModel()->getAll();
    }

    // ACTION
    public function edit($id = null)
    {
        // check list link
        $this->data['list_link'] = $this->getOption('list_link', $this->controller_name);

        $this->data['record'] = [];
        if ($id) {
            $this->data['record'] = $this->getModel()->get($id);
        }
    }
}
