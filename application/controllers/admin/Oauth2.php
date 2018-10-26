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


class Oauth2 extends AVC_AdminController
{
    // Action all clients
    public function index()
    {
        $this->data['records'] = $this->db->get('oauth_clients')->result();
    }
}