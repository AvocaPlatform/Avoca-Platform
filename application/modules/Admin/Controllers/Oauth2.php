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

namespace App\Modules\Admin\Controllers;


use Avoca\Controllers\AvocaAdminController;

class Oauth2 extends AvocaAdminController
{
    protected function init()
    {
        parent::init();

        $this->addGlobals([
            '_grant_types' => \Avoca\AvocaApiAuth::$grant_types,
        ]);
    }

    // Action all clients
    public function index()
    {
        // save client
        if ($this->isPost()) {
            $this->disableView();

            $client_id = $this->getPost('client_id');
            $client_secret = $this->getPost('client_secret');

            if (!$client_id || !$client_secret) {
                $this->setError(Avoca\AvocaRequestStatus::$InvalidParams);
                return $this->admin_redirect('/oauth2');
            }

            $data = [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_types' => 'password refresh_token',
                'scope' => 'read',
            ];

            $grant_types = $this->getPost('grant_types');
            if (!empty($grant_types)) {
                $data['grant_types'] = implode(' ', $grant_types);
            }

            $scope = $this->getPost('scope');
            if (!empty($scope)) {
                $data['scope'] = implode(' ', $scope);
            }

            $this->db->where('client_id', $client_id);
            $client = $this->db->get('oauth_clients')->row();
            if ($client) {
                $this->db->update('oauth_clients', $data, ['client_id' => $client_id]);
            } else {
                $this->db->insert('oauth_clients', $data);
            }

            $this->setSuccess(\Avoca\AvocaRequestStatus::$SaveRecordSuccess);
            return $this->admin_redirect('/oauth2');
        }

        $this->data['records'] = $this->db->get('oauth_clients')->result();
    }

    // Action
    public function edit_client($client_id = null)
    {
        $this->data['title'] = 'Create client';
        $this->data['client_id'] = $client_id;
        $this->data['record'] = new stdClass();

        // get all scopes
        $this->data['oauth_scopes'] = [];
        $scope_query = $this->db->get('oauth_scopes');
        foreach ($scope_query->result() as $item) {
            $this->data['oauth_scopes'][$item->scope] = $item->scope;
        }

        if ($client_id) {
            $this->data['title'] = 'Edit client';

            $this->db->where('client_id', $client_id);
            $record = $this->db->get('oauth_clients')->row();

            if (!$record) {
                $this->setError('Can not found this record');
                return $this->admin_redirect('/oauth2');
            }

            try {
                $record->grant_types_selected = explode(' ', $record->grant_types);
                $record->scope_selected = explode(' ', $record->scope);
            } catch (Exception $exception) {
                $record->grant_types_selected = [];
                $record->scope_selected = [];
            }

            $this->data['record'] = $record;
        }
    }

    // Action
    public function scopes()
    {
        // save scope
        if ($this->isPost()) {
            $this->disableView();

            $scope = $this->getPost('scope');
            $is_default = $this->getPost('is_default');
            $is_default = $is_default ? $is_default : 0;

            if ($scope) {
                $data = [
                    'scope' => $scope,
                    'is_default' => $is_default
                ];

                $this->db->where('scope', $scope);
                $record = $this->db->get('oauth_scopes')->row();

                if ($record) {
                    $this->db->update('oauth_scopes', $data, ['scope' => $scope]);
                } else {
                    $this->db->insert('oauth_scopes', $data);
                }

                $this->setSuccess(\Avoca\AvocaRequestStatus::$SaveRecordSuccess);
            } else {
                $this->setError(\Avoca\AvocaRequestStatus::$InvalidParams);
            }

            $this->admin_redirect('/oauth2/scopes');
        }

        $this->data['records'] = $this->db->get('oauth_scopes')->result();
    }

    // Action
    public function edit_scope($scope = null)
    {
        $this->data['title'] = 'Create scope';
        $this->data['scope'] = $scope;
        $this->data['record'] = new stdClass();

        if ($scope) {
            $this->data['title'] = 'Edit scope';

            $this->db->where('scope', $scope);
            $this->data['record'] = $this->db->get('oauth_scopes')->row();
        }
    }

    // Action
    public function tokens()
    {
        $this->data['records'] = $this->db->get('oauth_access_tokens')->result();
    }

    // Action
    public function refresh_tokens()
    {
        $this->data['records'] = $this->db->get('oauth_refresh_tokens')->result();
    }

    // Action
    public function delete($type, $type_id)
    {
        $this->disableView();

        if ($type == 'client') {
            $this->db->where('client_id', $type_id);
            $record = $this->db->get('oauth_clients')->row();

            if ($record) {
                $this->db->where('client_id', $type_id);
                $this->db->delete('oauth_clients');

                $this->setSuccess(\Avoca\AvocaRequestStatus::$DeleteRecordSuccess);
            } else {
                $this->setError(\Avoca\AvocaRequestStatus::$NotFoundRecord);
            }
        }

        if ($type == 'scope') {
            $this->db->where('scope', $type_id);
            $record = $this->db->get('oauth_scopes')->row();

            if ($record) {
                $this->db->where('scope', $type_id);
                $this->db->delete('oauth_scopes');
            } else {
                $this->setError(\Avoca\AvocaRequestStatus::$NotFoundRecord);
            }

            $this->admin_redirect('/oauth2/scopes');
        }

        $this->admin_redirect('/oauth2');
    }

    // Action
    public function remove_expire_tokens()
    {
        $this->db->where('expires <', date('Y-m-d H:i:s'));
        $this->db->delete('oauth_access_tokens');

        $this->setSuccess('Remove expire tokens successful');
        return $this->admin_redirect('/oauth2/tokens');
    }

    // Action
    public function remove_expire_refresh_token()
    {
        $this->db->where('expires <', date('Y-m-d H:i:s'));
        $this->db->delete('oauth_refresh_tokens');

        $this->setSuccess('Remove expire refresh tokens successful');
        return $this->admin_redirect('/oauth2/refresh_tokens');
    }
}