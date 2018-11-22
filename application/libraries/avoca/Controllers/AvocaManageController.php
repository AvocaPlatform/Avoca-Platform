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

    /**
     * get config layout
     *
     * @param string $model
     * @return array|mixed
     */
    protected function getViewDefs($model = '')
    {
        $model = $model ? $model : $this->model;
        $uri_viewdef = 'config/models/' . $model . '/viewdefs.php';

        $layout_path_c = CUSTOMPATH . $uri_viewdef;
        $layout_path = APPPATH . $uri_viewdef;

        if (file_exists($layout_path_c)) {
            return include $layout_path_c;
        }

        if (file_exists($layout_path)) {
            return include $layout_path;
        }

        return [];
    }

    // ACTION list
    public function index()
    {
        $this->load->library('pagination');

        // check create link. default controller/action
        $this->data['list_link'] = $this->getOption('list_link', $this->controller_name);
        $this->data['create_link'] = $this->getOption('create_link', $this->controller_name . '/edit');
        $this->data['view_link'] = $this->getOption('create_link', $this->controller_name . '/detail/{ID}');
        $this->data['edit_link'] = $this->getOption('create_link', $this->controller_name . '/edit/{ID}');
        $this->data['delete_link'] = $this->getOption('delete_link', $this->controller_name . '/delete/{ID}');

        // sort
        $this->data['sort'] = [
            'field' => '',
            'order' => 'asc'
        ];

        // search
        $search_form = '/manage_templates/search_form.twig';
        $this->data['search_form'] = 'templates' . $search_form;
        if (file_exists(VIEWPATH . $this->getViewFolder() . '/custom' . $search_form)) {
            $this->data['search_form'] = 'custom' . $search_form;
        }

        // get records
        $list = $this->getModel()->getAll();
        $this->data['list'] = $list;

        $this->data['records'] = [];
        if ($list && !empty($list['records'])) {
            $this->data['records'] = $list['records'];
        }

        // get layout
        $viewdefs = $this->getViewDefs();

        if (!empty($viewdefs)) {
            $this->data['viewdefs'] = $viewdefs;
        }

        if (!empty($viewdefs['list'])) {
            $this->data['listdefs'] = $viewdefs['list'];
        }

        // pagination
    }

    // ACTION create/edit
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