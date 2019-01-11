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

use Avoca\Models\AvocaModel;

class User extends AvocaModel
{
    protected $table = 'users';
    protected $limit = 7;

    public static $ADMIN_TYPE = 9;
    public static $MANAGER_TYPE = 1;

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

    public function create($data)
    {
        if (!empty($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        } else {
            $data['password'] = $this->hashPassword('avoca.io');
        }

        return parent::create($data);
    }

    public function update($data)
    {
        unset($data['password']);
        return parent::update($data);
    }

    public function getByUsername($username)
    {
        return $this->get_where(['username' => $username]);
    }
}
