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

class User extends AVC_Model
{
    protected $table = 'users';

    /**
     * hash user passoword
     *
     * @param $password
     * @return string
     */
    public function hashPassword($password)
    {
        return md5($password);
    }

    /**
     * user login check
     *
     * @param $username
     * @param $password
     * @return array|bool|mixed|null
     */
    public function userLogin($username, $password)
    {
        $user = $this->get_where([
            'username' => $username,
            'password' => $this->hashPassword($password)
        ]);

        if (!$user) {
            return false;
        }

        return $user;
    }
}
