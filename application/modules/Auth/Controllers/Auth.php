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


namespace App\Modules\Auth\Controllers;


use Avoca\Controllers\AvocaController;

class Auth extends AvocaController
{
    // Action login
    public function index()
    {
        $this->addGlobal('_pageClass', 'login-page');

        if ($this->isLogin()) {
            return $this->redirect_return($this->getRequest('r'));
        }

        $this->data['title'] = __('Login');
        $this->data['return_url'] = $this->getQuery('r');

        $this->setTitle($this->data['title']);

        if ($this->isPost()) {

            $this->disableView();

            /** @var User $userModel */
            $userModel = $this->getModel('Users/User');

            $username = $this->getPost('username');
            $password = $this->getPost('password');

            if ($username && $password) {
                $user = $userModel->userLogin($username, $password);

                if ($user) {
                    $this->setSession([
                        'user_id' => $user['id'],
                        'user_username' => $user['username'],
                        'user_isadmin' => $user['is_admin'],
                    ]);

                    $this->setSuccess('Login successful');
                    return $this->redirect_return($this->getPost('return_url'));
                }

                $this->setError('Login error');
                return $this->redirect('/Auth?r=' . $this->getPost('return_url'));
            }

            $this->setError(\Avoca\AvocaRequestStatus::$InvalidParams);
            return $this->redirect('/Auth?r=' . $this->getPost('return_url'));
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