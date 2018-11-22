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


class AvocaManageController extends AvocaController
{
    protected $model = '';
    protected $require_auth = true;

    protected function authenticate()
    {
        if (!$this->isLogin()) {
            return false;
        }

        return true;
    }

    protected function authenticateError()
    {
        $this->setError('You must login to access this page');
        return $this->redirect('/auth?r=' . current_url());
    }

    /**
     * @param null $template
     * @return string
     */
    protected function fetch_display($template = null)
    {
        $this->autoGlobals();

        if (!$this->view_path) {

            $root_path = 'templates' . DIRECTORY_SEPARATOR;
            $view = $this->router->directory . strtolower($this->controller_name) . DIRECTORY_SEPARATOR . strtolower($this->action_name);

            if (!file_exists(VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $root_path . $view . '.twig')) {

                $view = 'manage_templates' . DIRECTORY_SEPARATOR . strtolower($this->action_name);

                if (!file_exists(VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $root_path . $view . '.twig')) {
                    show_error('ERROR template: ' . VIEWPATH . $this->getViewFolder() . DIRECTORY_SEPARATOR . $root_path . $view . '.twig');
                }
            }

            $this->view_path = $view;
        }

        return parent::fetch_display($template);
    }

    /**
     * @param string $modelName
     * @return \AVC_Model
     */
    protected function getModel($modelName = '')
    {
        if (empty($modelName)) {
            $modelName = $this->model;
        }

        return parent::getModel($modelName);
    }

    // ACTION
    public function index()
    {
        // check create link. default controller/action
        $this->data['create_link'] = $this->getOption('create_link', $this->controller_name . '/edit');

        $this->data['records'] = $this->getModel()->getAll();
    }

    // ACTION
    public function edit($id = null)
    {
        // check list link
        $this->data['list_link'] = $this->getOption('list_link', $this->controller_name);

        $this->data['record'] = [];
        if ($id) {
            $this->data['record'] = $this->getModel()->get($id);
        }
    }
}