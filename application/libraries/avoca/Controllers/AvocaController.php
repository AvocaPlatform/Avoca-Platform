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
 * Class AvocaController
 * @package Avoca\Libraries\Controllers
 * @property \Twig $twig
 */
class AvocaController extends AvocaBaseController
{
    protected $view_type = 'html';
    protected $view_path = '';
    protected $view_disable = false;

    protected $options = [];
    protected $data = [];

    protected $css = [];
    protected $js = [];

    protected $isCacheView = false;

    protected $require_auth = false;

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

    protected $page_title = '';

    protected function init()
    {
        $this->loadTwig();
        $this->addGlobals([
            '_start_rtime' => microtime(true),
            '_controller' => $this->controller_name,
            '_action' => $this->action_name,
        ]);

        $this->setViewType();
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

    protected function loadTwig()
    {
        $this->load->config('twig');
        $twig = config_item('twig');

        $config = [
            'paths' => [$this->getViewPath()],
            'functions' => $twig['functions'],
            'functions_safe' => $twig['functions_safe'],
        ];

        $this->load->library('twig', $config);
    }

    /**
     * set page title
     *
     * @param $title
     * @param bool $translate
     */
    protected function setTitle($title, $translate = false)
    {
        if ($translate) {
            $this->page_title = $this->lang->line($title);
        } else {
            $this->page_title = $title;
        }
    }

    /**
     * get page title
     *
     * @return string
     */
    protected function getTitle()
    {
        $page_title = 'Avoca IO';
        if ($this->page_title) {
            $page_title = $this->page_title . ' | ' . $page_title;
        }

        return $page_title;
    }

    /**
     * add variable global for view
     *
     * @param $name
     * @param $value
     */
    protected function addGlobal($name, $value)
    {
        $this->twig->addGlobal($name, $value);
    }

    /**
     * add multi global variable for view
     *
     * @param $vars
     */
    protected function addGlobals($vars)
    {
        foreach ($vars as $var => $val) {
            $this->twig->addGlobal($var, $val);
        }
    }

    protected function getViewFolder()
    {
        return config_item('view_folder');
    }

    protected function getViewPath()
    {
        return VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR;
    }

    protected function getFilePath($uri_path)
    {
        // check custom folder
        $custom = CUSTOMPATH . $uri_path;
        if (file_exists($custom)) {
            return $custom;
        }

        return APPPATH . $uri_path;
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

    /**
     * show json to view
     *
     * @param null $data
     * @return bool
     */
    protected function jsonData($data = null)
    {
        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->httpCode, $this->httpCodeText));
        header('Content-Type: application/json');

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode($this->data);
        }

        return true;
    }

    /**
     * @param null $template
     * @return string
     */
    protected function fetch_display($template = null)
    {
        $this->autoGlobals();

        if (!$this->view_path) {
            $this->view_path = $this->router->directory . strtolower($this->controller_name) . DIRECTORY_SEPARATOR . strtolower($this->action_name);
        }

        return $this->twig->render($this->fixedViewPath($template), $this->data);
    }

    /**
     * render to view
     *
     * @param bool $return
     * @return string
     */
    protected function display($return = false)
    {
        if ($return) {
            return $this->fetch_display();
        }

        echo $this->fetch_display();
        exit(0);
    }

    /**
     * check custom view from folder view/<>/custom
     *
     * @param null $template
     * @return string
     */
    protected function fixedViewPath($template = null)
    {
        $template = $template ? $template : $this->view_path;

        $view_path = 'templates' . DIRECTORY_SEPARATOR . $template;
        $custom_path = 'custom' . DIRECTORY_SEPARATOR . $template;

        if (file_exists(VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $custom_path . '.twig')) {
            $view_path = $custom_path;
        }

        return $view_path;
    }

    /**
     * some global variable. auto set when render
     */
    protected function autoGlobals()
    {
        $this->addGlobals([
            '_pageTitle' => $this->getTitle(),
            '_ERRORS' => $this->errors,
            '_CSS' => $this->getCss(),
            '_JS' => $this->getJs(),
        ]);
    }

    protected function setFlash($message, $type = 'info')
    {
        if (is_array($message)) {
            $mes = '';
            foreach ($message as $m) {
                $mes .= $this->lang->line($m) . ' / ';
            }
        } else {
            $this->session->set_flashdata($type, $this->lang->line($message));
        }
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

    /**
     * redirect to return url
     *
     * @param $url string url
     * @param string $uri_default
     * @return bool
     */
    protected function redirect_return($url, $uri_default = '/')
    {
        if ($url) {
            if (strpos($url, 'http') !== false) {
                return $this->redirect($url);
            }
        }

        return $this->redirect($uri_default);
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        if ($this->detectMethod() == 'options') {
            return true;
        }

        if ($this->is_error) {
            return false;
        }

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