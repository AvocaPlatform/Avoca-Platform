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
    protected function authenticate()
    {
        if (!$this->isLogin()) {
            return false;
        }

        $user_idadmin = $this->getSession('user_isadmin');
        if ($user_idadmin != 2) {
            return false;
        }

        return true;
    }

    protected function authenticateError()
    {
        $this->setError('Only admin must access this page');
        return $this->redirect('/');
    }

    /**
     * @param bool $return
     * @throws \Exception
     */
    protected function display($return = false)
    {
        parent::display($return);
    }
}