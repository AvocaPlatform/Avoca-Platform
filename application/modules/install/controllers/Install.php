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


class Install extends AVC_Controller
{
    private $installed = true;

    // action install
    public function index()
    {
        $this->setTitle('Install Avoca Framework');

        if ($this->isPost()) {
            if ($this->installed) {
                $this->InstallAvoca();
            } else {
                return $this->redirect('/');
            }
        }
    }

    private function InstallAvoca()
    {
        $this->disableView();

        $db_host = $this->getPost('db_host');
        $db_username = $this->getPost('db_username');
        $db_password = $this->getPost('db_password');
        $db_database = $this->getPost('db_database');

        $title = $this->getPost('title');
        $base_url = $this->getPost('base_url');

        if ($db_host && $db_username && $db_database) {
            $template = file_get_contents(APPPATH . 'config/avoca/builders/config_database.avc');

            $template = str_replace([
                '$$HOST$$',
                '$$USERNAME$$',
                '$$PASSWORD$$',
                '$$DATABASE$$',
            ], [
                $db_host,
                $db_username,
                $db_password,
                $db_database,
            ], $template);

            write_file(APPPATH . 'config/database.php', $template, 'w');
        }

        // create database
        $install_sql = file_get_contents(APPPATH . 'config/avoca/upgrade/install_database.sql');
        $oauth2_sql = file_get_contents(APPPATH . 'config/avoca/upgrade/oauth2_database.sql');

        $this->importSQL($install_sql);
        $this->importSQL($oauth2_sql);

        // insert user admin
        $this->db->insert_batch('users', [
            [
                'id' => 1,
                'username' => 'avoca',
                'password' => '74de764fbf9324bc7ed97e219701dcc2',
                'is_admin' => 9,
            ]
        ]);

        // insert setting
        $this->db->insert_batch('settings', [
            [
                'category' => 'system',
                'name' => 'title',
                'value' => $title
            ], [
                'category' => 'system',
                'name' => 'base_url',
                'value' => $base_url
            ]
        ]);

        $this->setSuccess('Install successful');
        return $this->redirect('/auth');
    }

    private function importSQL($sql)
    {
        $sqls = explode(';', $sql);
        array_pop($sqls);

        foreach ($sqls as $statement) {
            $statement = $statement . ';';
            $this->db->query($statement);
        }
    }
}