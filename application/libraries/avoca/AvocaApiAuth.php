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

namespace Avoca\Libraries;


use OAuth2;

class AvocaApiAuth
{
    /** @var OAuth2\Server */
    private $server;

    /** @var OAuth2\Storage\Pdo */
    private $storage;

    /** @var OAuth2\Request */
    private $request;

    /** @var OAuth2\Response */
    private $response;

    public static $grant_types = [
        'password' => 'password',
        'refresh_token' => 'refresh_token',
        'client_credentials' => 'client_credentials',
        'authorization_code' => 'authorization_code',
    ];

    public function __construct()
    {
        $db = $this->getDBInfo();

        // $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
        $this->storage = new OAuth2\Storage\Pdo([
            'dsn' => $db['dsn'],
            'username' => $db['username'],
            'password' => $db['password'],
        ]);

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $this->server = new OAuth2\Server($this->storage);

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($this->storage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->server->addGrantType(new OAuth2\GrantType\AuthorizationCode($this->storage));

        $this->request = OAuth2\Request::createFromGlobals();
        $this->response = new OAuth2\Response();
    }

    /**
     * get db info from ci config
     *
     * @return array
     */
    private function getDBInfo()
    {
        $ci = &get_instance();
        $ci->load->database('avoca');

        return [
            'dsn' => "mysql:dbname={$ci->db->database};host={$ci->db->hostname}",
            'username' => $ci->db->username,
            'password' => $ci->db->password,
        ];
    }

    public function client_credentials()
    {
        $this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($this->storage, array(
            "allow_credentials_in_request_body" => true
        )));

        $this->server->handleTokenRequest($this->request)->send();
    }

    public function password_credentials()
    {
        $users = [
            'jacky' => [
                'password' => 'pass',
                'first_name' => 'homeway',
                'last_name' => 'yao'
            ]
        ];

        $storage = new OAuth2\Storage\Memory(array('user_credentials' => $users));
        $this->server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
        $this->server->handleTokenRequest($this->request)->send();
    }

    public function refresh_token()
    {
        $this->server->addGrantType(new OAuth2\GrantType\RefreshToken($this->storage, [
            "always_issue_new_refresh_token" => true,
            "unset_refresh_token_after_use" => true,
            "refresh_token_lifetime" => 2419200,
        ]));

        $this->server->handleTokenRequest($this->request)->send();
    }

    public function require_scope($scope = "")
    {
        if (!$this->server->verifyResourceRequest($this->request, $this->response, $scope)) {

            /** @var Oauth2\Response $response */
            $response =  $this->server->getResponse();

            return [
                'status' => $response->getStatusCode(),
                'statusText' => $response->getStatusText(),
                'params' => $response->getParameters(),
            ];
        }

        return ['status' => 200];
    }
}