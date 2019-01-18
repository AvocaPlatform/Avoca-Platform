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

namespace App\Modules\ApiVer1\Controllers;


use Avoca\Controllers\AvocaBaseController;

class Auth extends AvocaBaseController
{
    /** @var \Avoca\AvocaApiAuth */
    private $server;

    public function __construct()
    {
        parent::__construct();

        $this->server = new \Avoca\AvocaApiAuth();
    }

    /**
     * ACTION password_credentials
     * post form-data: {client_id: '<client_id>', client_secret:'<client_secret>', grant_type:'password', username: '<username>', password: '<password>'}
     * return: {"access_token":"<access_token>","expires_in":3600,"token_type":"Bearer","scope":"<scope>","refresh_token":"<refresh_token>"}
     */
    public function index()
    {
        $userAuth = [];

        /** @var User $userModel */
        $userModel = $this->getModel('Users/User');

        $username = $this->getPost('username');
        $password = $this->getPost('password');

        if ($username && $password) {
            $user = $userModel->userLogin($username, $password);
            avoca_log($user);

            if ($user) {
                $userAuth = [
                    $user['username'] => [
                        'password' => $password,
                        'first_name' => 'Avoca',
                        'last_name' => 'Platform',
                    ]
                ];
            }
        }

        $this->server->password_credentials($userAuth);
    }

    /**
     * ACTION refresh_token
     * post form-data: {refresh_token: "<refresh_token>", client_id: '<client_id>', client_secret:'<client_secret>', grant_type:'refresh_token'}
     * return: {"access_token":"<access_token>","expires_in":3600,"token_type":"Bearer","scope":"<scope>","refresh_token":"<refresh_token>"}
     */
    public function refresh_token()
    {
        $this->server->refresh_token();
    }

    /**
     * ACTION check access_token
     * request: ?access_token
     */
    public function resource()
    {
        $scope = !empty($_REQUEST['scope']) ? $_REQUEST['scope'] : '';
        $result = $this->server->require_scope($scope);

        header('Content-Type: application/json');

        if (empty($result) || $result['status'] != 200) {

            echo json_encode([
                'success' => false,
                'message' => $result['params'],
            ]);

            die();
        }

        echo json_encode(array(
            'success' => true,
            'message' => 'You accessed my APIs!',
        ));
    }
}