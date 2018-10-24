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

/**
 * Class AVC_Model
 * @property CI_DB_query_builder $db
 */
class AVC_Model extends CI_Model
{
    protected $table = '';

    protected $errors = [];

    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    protected function init()
    {

    }

    public function getName()
    {
        return get_class($this);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setErrors($messages)
    {
        if (is_array($messages)) {
            $this->errors = array_merge($this->errors, $messages);
        } else if (is_string($messages)) {
            $this->errors[] = $messages;
        }
    }

    /**
     * check and set error when query database
     *
     * @return bool
     */
    public function checkErrorDB()
    {
        $error = $this->db->error();
        if (!empty($error['code'])) {
            $this->setErrors($error['code'] . '. ' . $error['message']);
            return true;
        }

        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * create a record from this table
     *
     * @param $data
     * @return bool
     */
    public function create($data)
    {
        if (!$this->table) {
            $this->setErrors('Table name can not empty');
            return false;
        }

        // get all fields in table
        $fields = $this->db->list_fields($this->table);

        $fields_data = [
            'date_created' => date('Y-m-d H:i:s')
        ];

        // choose/reformat fields from data
        foreach ($fields as $field) {

            if (!empty($data[$field]) && $field != 'id') {

                $fields_data[$field] = $this->reformat($field, $data[$field]);
            }
        }

        // check valid fields
        $valid = $this->valid($fields_data);

        if (!empty($valid['error'])) {

            $this->setErrors($valid['message']);
            return false;
        }

        $this->db->set($fields_data);
        $this->db->insert($this->table);

        if (!$this->checkErrorDB()) {
            return false;
        }

        // return record id
        return $this->db->insert_id();
    }

    /**
     * update a record in table. when update data need to have id field
     *
     * @param $data
     * @return bool
     */
    public function update($data)
    {
        if (!$this->table) {
            $this->setErrors('Table name can not empty');
            return false;
        }

        // when update data need to have id field
        if (empty($data['id'])) {
            $this->setErrors('ID can not found');
            return false;
        }

        // get all fields in table
        $fields = $this->db->list_fields($this->table);

        $fields_data = [];

        // choose/reformat data fields
        foreach ($fields as $field) {

            if (!empty($data[$field]) && $field != 'id') {

                $fields_data[$field] = $this->reformat($field, $data[$field]);
            }
        }

        // valid fields
        $valid = $this->valid($fields_data);

        if (!empty($valid['error'])) {

            $this->setErrors($valid['message']);
            return false;
        }

        $id = $data['id'];
        $this->db->where('id', $id);
        $this->db->set($fields_data);
        $this->db->update($this->table);

        if (!$this->checkErrorDB()) {
            return false;
        }

        return $id;
    }

    /**
     * valid fields in table
     *
     * @param $data
     * @return array
     */
    protected function valid($data)
    {
        return [
            'error' => 0
        ];
    }

    /**
     * reformat field value before insert or update to database
     *
     * @param $field_name
     * @param $field_value
     * @return string
     */
    protected function reformat($field_name, $field_value)
    {
        if (is_array($field_value) || is_object($field_value)) {
            return json_encode($field_value);
        }

        return $field_value;
    }

    /**
     * format fields when display to client
     *
     * @param $record
     * @param bool $is_array
     * @return mixed
     */
    protected function displayRecord($record, $is_array = false)
    {
        return $record;
    }

    /**
     * set this->db->limit
     *
     * @param int $limit
     * @param int $offset
     */
    protected function setDBLimit($limit = 0, $offset = 0)
    {
        if ($limit >= 0) {
            if ($limit == 0) {
                $limit = config_item('records_per_page');
            }

            $this->db->limit($limit, $offset);
        }
    }

    /**
     * get a record by id in table and format before render
     *
     * @param $id
     * @param string $table
     * @return mixed|null
     */
    public function get($id, $table = '')
    {
        $table = ($table) ? $table : $this->table;

        $this->db->where('id', $id);
        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            return $this->displayRecord($query->row());
        }

        return null;
    }

    /**
     * get data from where. return record array when row == true and array object when row == false
     * @param $where
     * @param bool $row
     * @param string $table
     * @param int $offset
     * @param int $limit ==-1 will show all records
     * @return array|mixed|null
     */
    public function get_where($where = '', $row = true, $table = '', $offset = 0, $limit = 0)
    {
        if ($where) {
            $this->db->where($where);
        }

        $table = ($table) ? $table : $this->table;

        $total_records = $this->db->get($table)->num_rows();

        $this->setDBLimit($limit, $offset);
        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            if ($row == 0) {
                return $this->displayRecord($query->row_array());
            }

            $data = [
                'total' => $total_records,
                'count' => $query->num_rows(),
                'records' => []
            ];

            $records = $query->result_array();

            foreach ($records as $record) {
                $data['records'][] = $this->displayRecord($record);
            }

            return $data;
        }

        return [
            'total' => $total_records,
            'count' => 0,
            'records' => []
        ];
    }

    /**
     * get all records in table
     *
     * @param string $table
     * @param int $offset
     * @return array|mixed|null
     */
    public function getAll($table = '', $offset = 0)
    {
        return $this->get_where('', false, $table, $offset);
    }
}