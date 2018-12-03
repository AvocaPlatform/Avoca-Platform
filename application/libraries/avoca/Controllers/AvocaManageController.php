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

            if (!file_exists($this->getViewPath() . $root_path . $view . '.twig')) {
                $view = 'manage_templates' . DIRECTORY_SEPARATOR . strtolower($this->action_name);
                if (!file_exists($this->getViewPath() . $root_path . $view . '.twig')) {
                    show_error('ERROR template: ' . $this->getViewPath() . $root_path . $view . '.twig');
                }
            }

            $this->view_path = $view;
        } else {
            $root_path = 'templates' . DIRECTORY_SEPARATOR;
            $view = $this->router->directory . $this->view_path;

            if (!file_exists($this->getViewPath() . $root_path . $view . '.twig')) {
                $view = 'manage_templates' . DIRECTORY_SEPARATOR . $this->view_path;
                if (!file_exists($this->getViewPath() . $root_path . $view . '.twig')) {
                    show_error('ERROR template: ' . $this->getViewPath() . $root_path . $view . '.twig');
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

        $layout_path = $this->getFilePath($uri_viewdef);

        if (file_exists($layout_path)) {
            return include $layout_path;
        }

        return [];
    }

    /**
     * get search fields from list view defs
     *
     * @param array $listdefs
     * @return array
     */
    protected function getSearchFields($listdefs)
    {
        $fields = [];

        if (!is_array($listdefs) || empty($listdefs)) {
            return $fields;
        }

       foreach ($listdefs as $field => $option) {
           if (!empty($option['search'])) {
               $operator = '';
               if (!empty($option['operator'])) {
                   $operator = $option['operator'];
               }

               $fields[$field] = $operator;
           }
       }

       return $fields;
    }

    /**
     * get array where search
     *
     * @param $searchFields
     * @param $get
     * @param string $where
     * @return array|string
     */
    protected function whereSearch($searchFields, $get, $where = '')
    {
        $whereSearch = [];
        foreach ($searchFields as $field => $operator) {
            if (isset($get[$field]) && $get[$field] != '') {
                $field_where = $field;
                if ($operator) {
                    $field_where = $field . ' ' . $operator;
                }

                $value = $get[$field];
                if ($operator == 'like') {
                    $value = '%' . $value . '%';
                }

                $whereSearch[$field_where] = $value;
            }
        }

        if (!empty($whereSearch)) {
            if (!$where) {
                return $whereSearch;
            }

            if (is_array($where)) {
                return array_merge($whereSearch, $where);
            }
        }

        return $where;
    }

    /**
     * add js and css from viewdefs
     */
    protected function addStatic()
    {
        $viewdefs = $this->getViewDefs();

        if (!empty($viewdefs['js'])) {
            $this->addJs($viewdefs['js']);
        }

        if (!empty($viewdefs['css'])) {
            $this->addCss($viewdefs['css']);
        }
    }

    /**
     * ACTION module dashboard
     */
    public function index()
    {
        $this->records();
    }

    // ACTION list
    public function records()
    {
        $this->view_path = 'records';
        $this->load->library('pagination');

        if (!$this->page_title) {
            $this->setTitle(ucfirst($this->controller_name), true);
        }

        $this->addStatic();

        // check create link. default controller/action
        $this->data['list_link'] = $this->getOption(\ControllerOptions::LIST_LINK, $this->controller_name . '/records');
        $this->data['create_link'] = $this->getOption(\ControllerOptions::CREATE_LINK, $this->controller_name . '/edit');
        $this->data['view_link'] = $this->getOption(\ControllerOptions::VIEW_LINK, $this->controller_name . '/record/{ID}');
        $this->data['edit_link'] = $this->getOption(\ControllerOptions::EDIT_LINK, $this->controller_name . '/edit/{ID}');
        $this->data['delete_link'] = $this->getOption(\ControllerOptions::DELETE_LINK, $this->controller_name . '/delete/{ID}');
        $this->data['delete_batch_link'] = $this->getOption(\ControllerOptions::DELETE_BATCH_LINK, $this->controller_name . '/delete');

        // get layout
        $viewdefs = $this->getViewDefs();
        if (!empty($viewdefs)) {
            $this->data['viewdefs'] = $viewdefs;
        }

        if (!empty($viewdefs['list'])) {
            $this->data['listdefs'] = $viewdefs['list'];
        }

        // sort
        $orders = [];
        $order_by = $this->getQuery('order_by');
        if ($order_by) {
            $order_type = $this->getQuery('order');
            if (!$order_type || !in_array($order_type, ['asc', 'desc'])) {
                $order_type = 'asc';
            }
            // get orders
            $orders = [$order_by => $order_type];
            $this->data['sort'] = [
                'field' => $order_by,
                'order' => $order_type
            ];
        }

        // default where
        $where = $this->getOption(\ControllerOptions::LIST_WHERE, '');

        // search
        $search_form = '/manage_templates/search_form.twig';
        $this->data['search_form'] = 'templates' . $search_form;
        if (file_exists(VIEWPATH . $this->getViewFolder() . '/custom' . $search_form)) {
            $this->data['search_form'] = 'custom' . $search_form;
        }
        // query search
        if (!empty($viewdefs['list'])) {
            $searchFields = $this->getSearchFields($viewdefs['list']);
            $where = $this->whereSearch($searchFields, $this->getQuery(), $where);
        }

        // get records
        $model = $this->getModel();

        $offset = $this->uri->segment(4);
        $list = $model->getRecords($where, $offset, $orders);

        $this->data['list'] = $list;
        $this->data['model_name'] = $this->model;

        $this->data['records'] = [];
        if ($list && !empty($list['records'])) {
            $this->data['records'] = $list['records'];
        }

        // pagination
        $pagination_config = include APPPATH . 'config/avoca/pagination.php';
        $pagination_config['base_url'] = avoca_manage($this->data['list_link']);
        $pagination_config['uri_segment'] = 4;
        $pagination_config['total_rows'] = $list['total'];
        $pagination_config['per_page'] = $model->getLimit();
        $this->pagination->initialize($pagination_config);
        $this->data['pagination'] = $this->pagination->create_links();

        $return_url = $this->getQuery('r');
        if (!$return_url) {
            $return_url = avoca_currentUrl();
        }

        $this->data['return_url'] = $return_url;
    }

    // ACTION view detail record
    public function record($id)
    {
        $this->view_path = 'record';

        // check create link. default controller/action
        $this->data['list_link'] = $this->getOption(\ControllerOptions::LIST_LINK, $this->controller_name . '/records');
        $this->data['edit_link'] = $this->getOption(\ControllerOptions::EDIT_LINK, $this->controller_name . '/edit/{ID}');
        $this->data['delete_link'] = $this->getOption(\ControllerOptions::DELETE_LINK, $this->controller_name . '/delete/{ID}');

        // get record
        $model = $this->getModel();
        $record = $model->get($id);

        if (!$record) {
            $this->setError('Can not found this record');
            return $this->manage_redirect('/' . $this->controller_name);
        }

        $this->data['record'] = $record;

        // viewdefs
        $viewdefs = $this->getViewDefs();
        $this->data['viewdefs'] = $viewdefs;

        if (!empty($viewdefs['record'])) {
            $this->data['recorddefs'] = $viewdefs['record'];
        }

        $this->setTitle(recordVal($record, $viewdefs['title']));
        $this->addStatic();
    }

    // ACTION create/edit
    public function edit($id = null)
    {
        $this->view_path = 'edit';
        $page_title = $this->lang->line('Create new') . ' ' . $this->lang->line(ucfirst($this->controller_name));

        // check create link. default controller/action
        $this->data['list_link'] = $this->getOption(\ControllerOptions::LIST_LINK, $this->controller_name . '/records');
        $this->data['view_link'] = $this->getOption(\ControllerOptions::VIEW_LINK, $this->controller_name . '/record/{ID}');
        $this->data['delete_link'] = $this->getOption(\ControllerOptions::DELETE_LINK, $this->controller_name . '/delete/{ID}');

        // viewdefs
        $viewdefs = $this->getViewDefs();
        $this->data['viewdefs'] = $viewdefs;

        if (!empty($viewdefs['record'])) {
            $this->data['recorddefs'] = $viewdefs['record'];
        }

        $this->data['record'] = [];
        if ($id) {
            $record = $this->getModel()->get($id);
            $this->data['record'] = $record;
            if (!$this->page_title) {
                $key_title = (!empty($viewdefs['title'])) ? $viewdefs['title'] : 'id';
                $page_title = $this->lang->line('Edit') . ': ' . $record[$key_title];
            }
        }

        $this->setTitle($page_title);
        $this->addStatic();
    }

    // ACTION save
    public function save($ajax = null)
    {
        $this->disableView();

        if ($this->isPost()) {
            $post = $this->getPost();

            $model_name = $this->model;
            if (!empty($post['model'])) {
                $model_name = $post['model'];
            }

            $model = $this->getModel($model_name);
            $id = $model->save($post);

            // AJAX
            if ($ajax == '1') {
                if ($id) {
                    return $this->jsonData([
                        'error' => 0,
                        'id' => $id
                    ]);
                }

                return $this->jsonData([
                    'error' => 1,
                    'errors' => $model->getErrors(),
                ]);
            }

            // VIEW
            if ($id) {
                $this->setSuccess('Save record success!');
                $return_url = $this->getPost('return_url')
                    ?: $this->getOption(\ControllerOptions::SAVE_RETURN_URL)
                        ?: '/' . $this->controller_name . '/record/' . $id;
            } else {
                $this->setError($model->getErrors());

                if (!empty($post['id'])) {
                    $return_url = $this->getOption(\ControllerOptions::SAVE_RETURN_URL)
                        ?: '/' . $this->controller_name . '/edit/' . $post['id'];
                } else {
                    $return_url = $this->getOption(\ControllerOptions::SAVE_RETURN_URL)
                        ?: '/' . $this->controller_name . '/edit';
                }
            }

            return $this->manage_redirect($return_url);
        }

        $return_url = $this->getOption('list_link', '/' . $this->controller_name);
        return $this->manage_redirect($return_url);
    }

    // ACTION delete
    public function delete($id = null)
    {
        $this->disableView();
        if ($this->isPost()) {
            $ids = $this->getPost('ids');

            $model = $this->getModel();
            $model->delete($ids);
            $errors = $model->getErrors();
            if (empty($errors)) {
                $this->setSuccess('Deleted records successful');
            } else {
                $this->setError($errors);
            }

            $return_url = $this->getPost('r');
            if (!$return_url) {
                $return_url = $this->getOption(\ControllerOptions::LIST_LINK, '/' . $this->controller_name);
                return $this->manage_redirect($return_url);
            }

            return $this->redirect($return_url);
        }

        if ($id) {
            $model = $this->getModel();
            $model->delete($id);
            $errors = $model->getErrors();
            if (empty($errors)) {
                $this->setSuccess('Deleted record successful');
            } else {
                $this->setError($errors);
            }
        }

        $return_url = $this->getQuery('r');
        if (!$return_url) {
            $return_url = $this->getOption(\ControllerOptions::LIST_LINK, '/' . $this->controller_name);
            return $this->manage_redirect($return_url);
        }

        return $this->redirect($return_url);
    }
}