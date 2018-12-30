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

class Users extends AVC_APIV1Controller
{
    protected $model = 'user';

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
                $userModel = $this->getModel('user');
                $this->data['user'] = $userModel->getByUsername($username);
            }
        }
    }
}