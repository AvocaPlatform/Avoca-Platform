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


class AvocaAdminController extends AvocaController
{
    protected $require_auth = true;

    protected function authenticate()
    {
        if (!$this->isLogin()) {
            return false;
        }

        $user_idadmin = $this->getSession('user_isadmin');
        if ($user_idadmin == \User::$ADMIN_TYPE) {
            return true;
        }

        return false;
    }

    protected function authenticateError()
    {
        if ($this->isLogin()) {
            $this->setError('Only admin must access this page');
            return $this->redirect('/');
        }

        $this->setError('Please login!');
        return $this->redirect('/auth');
    }

    /**
     * @param bool $return
     * @throws \Exception
     */
    protected function display($return = false)
    {
        parent::display($return);
    }

    /**
     * check is user admin of manage page
     *
     * @return bool
     */
    public function isManager()
    {
        $user_idadmin = $this->getSession('user_isadmin');
        if ($user_idadmin == \User::$MANAGER_TYPE) {
            return true;
        }

        return false;
    }
}