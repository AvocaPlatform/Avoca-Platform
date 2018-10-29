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
    protected $require_auth = false;

    // Action login
    public function index()
    {
        if ($this->isPost()) {

            /** @var User $userModel */
            $userModel = $this->getModel('user');

            $username = $this->getPost('username');
            $password = $this->getPost('password');

            if ($username && $password) {
                $user = $userModel->userLogin($username, $password);

                if ($user) {
                    $this->setSession([
                        'user_id' => $user->id,
                        'user_username' => $user->username
                    ]);

                    $this->setSuccess('Login successful');
                    return $this->redirect('/');
                }

                $this->setError('Login error');
                return $this->redirect('/auth');
            }
        }
    }

    // Action logout
    public function logout()
    {
        $this->session->sess_destroy();
        $this->setSuccess('Logout successful');
        return $this->redirect('/');
    }
}