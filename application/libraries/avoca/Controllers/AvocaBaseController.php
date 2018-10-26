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
 */
class AvocaBaseController extends \CI_Controller
{
    protected $controller_name;
    protected $action_name;

    public function __construct()
    {
        parent::__construct();

        $this->controller_name = $this->router->fetch_class();
        $this->action_name = $this->router->fetch_method();

        $this->init();
    }

    protected function init()
    {

    }

    /**
     * check request method is post
     *
     * @return bool
     */
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

    protected function redirect($uri, $method = 'auto', $code = null)
    {
        redirect($uri, $method, $code);
        return true;
    }
}
