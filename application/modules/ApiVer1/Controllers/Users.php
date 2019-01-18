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


use Avoca\Controllers\AvocaApiV1Controller;

class Users extends AvocaApiV1Controller
{
    protected $model = 'Users/User';

    /**
     * Action
     *
     * @param $token string
     */
    public function profile($token)
    {
        $this->data['user_info'] = 1;
        if ($token) {
            $this->db->where('access_token', $token);
            $query = $this->db->get('oauth_access_tokens');
            if ($query->num_rows() > 0) {
                $oauth_token = $query->row_array();
                $username = $oauth_token['user_id'];

                /** @var \User $userModel */
                $userModel = $this->getModel('Users/User');
                $this->data['user'] = $userModel->getByUsername($username);
            }
        }
    }
}