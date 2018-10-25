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

class Auth extends AVC_Controller
{
    /** @var \Avoca\Libraries\AVC_ApiAuth */
    private $server;

    public function __construct()
    {
        parent::__construct();

        $this->server = new \Avoca\Libraries\AVC_ApiAuth();
    }

    public function index()
    {

    }

    public function token()
    {
        $this->disableView();

        $this->server->authorization_code();
    }

    public function client_credentials()
    {
        $this->disableView();

        $this->server->client_credentials();
    }

    public function password_credentials()
    {
        $this->disableView();

        $this->server->password_credentials();
    }

    public function refresh_token()
    {
        $this->disableView();

        $this->server->refresh_token();
    }

    public function resource()
    {
        $this->disableView();

        $this->server->require_scope("userinfo cloud file node");//you can require scope here

        echo json_encode(array(
            'success' => true,
            'message' => 'You accessed my APIs!'
        ));
    }
}