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


namespace App\Modules\Users\Controllers;


use Avoca\Controllers\AvocaApiV1Controller;

class ApiV1 extends AvocaApiV1Controller
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