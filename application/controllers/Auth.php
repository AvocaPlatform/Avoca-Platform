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
    // Action login
    public function index()
    {
        if ($this->isLogin()) {
            return $this->redirect_return($this->getRequest('r'));
        }

        $this->data['title'] = __('Login');
        $this->data['return_url'] = $this->getQuery('r');

        if ($this->isPost()) {

            $this->disableView();

            /** @var User $userModel */
            $userModel = $this->getModel('user');

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
                    return $this->redirect_return($this->getRequest('r'));
                }

                $this->setError('Login error');
                return $this->redirect('/auth?r=' . $this->getRequest('r'));
            }

            $this->setError(\Avoca\Libraries\AvocaRequestStatus::$InvalidParams);
            return $this->redirect('/auth?r=' . $this->getRequest('r'));
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