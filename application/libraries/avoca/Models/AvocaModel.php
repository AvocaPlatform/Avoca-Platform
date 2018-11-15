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


namespace Avoca\Libraries\Models;


/**
 * Class AvocaModel
 * @property \CI_DB_query_builder $db
 */
class AvocaModel extends \CI_Model
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
     * create or update record
     *
     * @param $data
     * @return bool
     */
    public function save($data)
    {
        if (!$this->table) {
            $this->setErrors('Table name can not empty');
            return false;
        }

        if (empty($data['id'])) {
            return $this->create($data);
        }

        return $this->update($data);
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
        $record = $this->get($id);

        if ($record) {
            $this->db->where('id', $id);
            $this->db->set($fields_data);
            $this->db->update($this->table);

            if (!$this->checkErrorDB()) {
                return false;
            }
        } else {
            $this->setErrors('Record is not exist');
            return false;
        }

        return $id;
    }

    /**
     * delete record in database by id or array id
     *
     * @param $data
     */
    public function delete($data)
    {
        $ids = [];

        if (is_numeric($data)) {
            $ids = [$data];
        }

        if (is_array($data)) {
            $ids = $data;
        }

        foreach ($ids as $id) {
            $this->db->delete($this->table, array('id' => $id));
            $error = $this->db->error();
            if ($error['code']) {
                $this->setErrors(['Error delete ' . $id . ' in table: ' . $this->table]);
            }
        }
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
     * count num rows
     *
     * @param $where
     * @param string $table
     * @return int
     */
    public function numRows($where, $table = '')
    {
        $table = ($table) ? $table : $this->table;

        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->get($table)->num_rows();
    }

    /**
     * get data from where. return record array when row == true and array object when row == false
     * @param $where string | array
     * @param bool $row
     * @param string $table
     * @param int $offset
     * @param int $limit ==-1 will show all records
     * @return array|mixed|null
     */
    public function get_where($where = '', $row = true, $table = '', $offset = 0, $limit = 0)
    {
        $table = ($table) ? $table : $this->table;
        $total_records = $this->numRows($where, $table);

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->setDBLimit($limit, $offset);
        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            if ($row) {
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

        if ($row) {
            return null;
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