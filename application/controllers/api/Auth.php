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

class Auth extends AVC_BaseController
{
    /** @var \Avoca\Libraries\AvocaApiAuth */
    private $server;

    public function __construct()
    {
        parent::__construct();

        $this->server = new \Avoca\Libraries\AvocaApiAuth();
    }

    // ACTION password_credentials
    public function index()
    {
        $this->server->password_credentials();
    }

    // ACTION
    public function refresh_token()
    {
        $this->server->refresh_token();
    }

    // ACTION
    public function resource()
    {
        $this->server->require_scope();//you can require scope here

        echo json_encode(array(
            'success' => true,
            'message' => 'You accessed my APIs!'
        ));
    }
}