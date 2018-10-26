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


/**
 * Class AVC_APIController
 */
class AvocaApiController extends AvocaController
{
    protected $model = '';

    protected $view_type = 'json';
    protected $view_disable = true;

    protected $errors = [];

    protected function init()
    {

    }

    protected function authenticate()
    {

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
     * fixed when post by json
     *
     * @param null $name
     * @return array|mixed|string
     */
    protected function getPost($name = null)
    {
        $post = parent::getPost($name);
        if (empty($post)) {
            try {
                $post = json_decode(trim(file_get_contents('php://input')), true);
            } catch (Exception $exception) {
                $post = [];
            }
        }

        return $post;
    }

    protected function jsonData()
    {
        $json_arr = [
            'error' => 0,
            'message' => '',
            'data' => $this->data
        ];

        if (!empty($this->errors)) {
            $json_arr['error'] = 1;
            $json_arr['messages'] = $this->errors;
        }

        header('Content-Type: application/json');
        echo json_encode($json_arr);
        return true;
    }

    protected function apiErrors($messages)
    {
        if (is_string($messages)) {
            $this->errors[] = $messages;
        } else {
            $this->errors = array_merge($this->errors, $messages);
        }
    }

    // ACTION
    public function index($id = null)
    {
        // no param id
        if (!$id) {
            // create when post data
            if ($this->isPost()) {
                return $this->create();
            }

            // get all records when get
            return $this->records();
        }

        // update record when post
        if ($this->isPost()) {
            return $this->update($id);
        }

        // get record when get
        return $this->record($id);
    }

    /**
     * get all records
     *
     * @return bool
     */
    protected function records()
    {
        $this->data['records'] = $this->getModel()->getAll();
        return true;
    }

    /**
     * get detail record by id
     *
     * @param $id
     * @return bool
     */
    protected function record($id)
    {
        $this->data['record'] = $this->getModel()->get($id);
        return true;
    }

    /**
     * post data to create a record
     */
    protected function create()
    {
        $data = $this->getPost();

        $model = $this->getModel();
        $id = $model->save($data);
        $errors = $model->getErrors();

        if (empty($errors)) {
            $this->data['record'] = $model->get($id);
        } else {
            $this->apiErrors($errors);
        }

        return true;
    }

    /**
     * post data to update record
     *
     * @param $id
     * @return bool
     */
    protected function update($id)
    {
        $data = $this->getPost();
        $data['id'] = $id;

        $model = $this->getModel();
        $model->save($data);
        $errors = $model->getErrors();

        if (empty($errors)) {
            $this->data['record'] = $model->get($id);
        } else {
            $this->apiErrors($errors);
        }

        return true;
    }
}