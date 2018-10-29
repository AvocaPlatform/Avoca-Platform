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


class AvocaController extends AvocaBaseController
{
    use \CiPug;

    protected $view_type = 'html';
    protected $view_path = '';
    protected $view_disable = false;

    protected $options = [];
    protected $dataGlobal = [];
    protected $data = [];

    protected $css = [];
    protected $js = [];

    protected $isCacheView = false;

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

    protected function init()
    {
        $this->addGlobals([
            '_start_rtime' => microtime(true),
            '_controller' => $this->controller_name,
            '_action' => $this->action_name,
        ]);

        $this->setViewType();
        $this->setViewFolderPath();
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
                //'cache' => APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'pug'
            ]);

            if ($this->isCacheView) {
                $this->settings([
                    'cache' => APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'pug'
                ]);
            }
        } catch (Exception $e) {
            show_error('ERROR view path: ' . VIEWPATH);
        }
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

    /**
     * show json to view
     *
     * @return bool
     */
    protected function jsonData()
    {
        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->httpCode, $this->httpCodeText));
        header('Content-Type: application/json');

        echo json_encode($this->data);
        return true;
    }

    /**
     * render to view
     *
     * @param bool $return
     * @throws \Exception
     */
    protected function display($return = false)
    {
        $this->autoGlobals();

        if (!$this->view_path) {
            $this->view_path = $this->router->directory . strtolower($this->controller_name) . DIRECTORY_SEPARATOR . strtolower($this->action_name);
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
        $page_title = 'Avoca Framework';
        if (!empty($this->data['title'])) {
            $page_title = $this->data['title'] . ' | ' . $page_title;
        }

        $this->addGlobals([
            '_pageTitle' => $page_title,
            '_ERRORS' => $this->errors,
            '_CSS' => $this->getCss(),
            '_JS' => $this->getJs(),
        ]);
    }

    protected function setFlash($message, $type = 'info')
    {
        $this->session->set_flashdata($type, $this->lang->line($message));
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