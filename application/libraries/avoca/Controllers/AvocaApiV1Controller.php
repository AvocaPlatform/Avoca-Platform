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
     * @return bool
     */
    protected function jsonData()
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

        // delete a record
        if ($this->isDelete()) {
            $this->delete($id);
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
            $this->setError($errors);
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
            $this->setErrors($errors);
        }

        return true;
    }

    protected function delete($id)
    {

    }
}