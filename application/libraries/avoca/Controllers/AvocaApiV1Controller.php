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
class AvocaApiV1Controller extends AvocaController
{
    protected $model = '';
    protected $require_auth = true;

    /**
     * @var \Avoca\Libraries\AvocaApiAuth
     */
    protected $auth;

    /**
     * scope access this controller
     *
     * @var string
     */
    protected $scope = '';

    /**
     * list action no need authenticate
     *
     * @var array
     */
    protected $action_no_auth = [];

    protected $view_type = 'json';
    protected $view_disable = true;

    protected function init()
    {
        $this->auth = new \Avoca\Libraries\AvocaApiAuth();

        $this->addGlobals([
            '_start_rtime' => microtime(true),
        ]);
    }

    /**
     * check authenticate auto load if $require_auth = true.
     * $require_auth = false you must manual use for action
     *
     * @return bool
     */
    protected function authenticate()
    {
        if (!in_array($this->action_name, $this->action_no_auth)) {
            $result = $this->auth->require_scope($this->scope);

            if (empty($result) || $result['status'] != 200) {

                $this->data = [];
                $this->httpCode = $result['status'];
                $this->httpCodeText = $result['statusText'];
                $this->errors = $result['params'];

                return false;
            }
        }

        return true;
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
     * detect PUT request
     *
     * @return bool
     */
    protected function isPut()
    {
        if ($this->detectMethod() == 'put') {
            return true;
        }

        return false;
    }

    /**
     * detect DELETE request
     *
     * @return bool
     */
    protected function isDelete()
    {
        if ($this->detectMethod() == 'delete') {
            return true;
        }

        return false;
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
            } catch (\Exception $exception) {
                $post = [];
            }
        }

        return $post;
    }

    /**
     * API return json data
     *
     * @param null $data
     * @return bool
     */
    protected function jsonData($data = null)
    {
        $json_arr = [
            'error' => 0,
            'status' => $this->httpCode,
            'statusText' => $this->httpCodeText,
            'message' => [],
            'data' => $this->data,
        ];

        if ($this->httpCode != 200 || !empty($this->errors)) {
            $json_arr['error'] = 1;
            $json_arr['message'] = $this->errors;
        }

        header(sprintf('HTTP/%s %s %s', $this->version, $this->httpCode, $this->httpCodeText));
        header('Content-Type: application/json');

        echo json_encode($json_arr);
        return true;
    }

    /**
     * Action
     *
     * GET --> get list records
     * POST --> create new record
     *
     * @return bool
     */
    public function records()
    {
        // create when post data
        if ($this->isPost()) {
            return $this->createRecord();
        }

        // get all records when get
        return $this->listRecords();
    }

    /**
     * Action
     *
     * GET --> get detail record
     * PUT --> update record
     * DELETE --> delete record
     *
     * @param $id
     * @return bool
     */
    public function record($id)
    {
        // update record when post
        if ($this->isPost()) {
            return $this->updateRecord($id);
        }

        // delete a record
        if ($this->isDelete()) {
            $this->deleteRecord($id);
        }

        // get record when get
        return $this->getRecord($id);
    }

    /**
     * GET all records
     *
     * @return bool
     */
    protected function listRecords()
    {
        $this->data['list'] = $this->getModel()->getAll();
        return true;
    }

    /**
     * GET detail record by id
     *
     * @param $id
     * @return bool
     */
    protected function getRecord($id)
    {
        $this->data['record'] = $this->getModel()->get($id);
        return true;
    }

    /**
     * POST data to create a record
     */
    protected function createRecord()
    {
        $data = $this->getPost();

        $model = $this->getModel();
        $id = $model->save($data);
        $errors = $model->getErrors();

        if (empty($errors)) {
            $this->data['record'] = $model->get($id);
        } else {
            $this->setError($errors);
        }

        return true;
    }

    /**
     * PUT data to update record
     *
     * @param $id
     * @return bool
     */
    protected function updateRecord($id)
    {
        $data = $this->getPost();
        $data['id'] = $id;

        $model = $this->getModel();
        $model->save($data);
        $errors = $model->getErrors();

        if (empty($errors)) {
            $this->data['record'] = $model->get($id);
        } else {
            $this->setErrors($errors);
        }

        return true;
    }

    /**
     * DELETE to delete record
     *
     * @param $id
     */
    protected function deleteRecord($id)
    {
        $model = $this->getModel();
        $model->delete($id);

        $errors = $model->getErrors();
        if (!empty($errors)) {
            $this->setErrors($errors);
        }
    }
}