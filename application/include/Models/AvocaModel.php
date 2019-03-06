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


namespace Avoca\Models;


/**
 * Class AvocaModel
 * @property \CI_DB_query_builder $db
 * @property \AVC_Loader $load
 */
class AvocaModel extends \CI_Model
{
    protected $module = '';
    protected $table = '';
    protected $limit = 0;
    protected $relations = [
        'has_one' => [],
        'has_many' => [],
    ];

    protected $field_defs = null;
    protected $layout_defs = null;

    protected $errors = [];
    protected $fieldModel;

    public function __construct()
    {
        parent::__construct();

        $this->initModule();
        $this->init();
    }

    protected function init()
    {

    }

    protected function initModule()
    {
        $className = $this->getName();
        $arr = explode('\\', $className);
        $this->module = $arr[2];

        // set fields defs
        if (!$this->field_defs) {
            $path = 'modules/' . $this->module . '/Config/' . $this->getName(true) . '_vardefs.php';
            if (file_exists(CUSTOMPATH . $path)) {
                $this->field_defs = include CUSTOMPATH . $path;
            } else if (file_exists(APPPATH . $path)) {
                $this->field_defs = include APPPATH . $path;
            }
        }

        // set relation has one
        if (!empty($this->field_defs['fields'])) {
            foreach ($this->field_defs['fields'] as $field) {
                if ($field['type'] == 'relate') {
                    $this->relations['has_one'][$field['name']] = [
                        'local_key' => $field['name'],
                        'foreign_module' => $field['rmodule'],
                        'foreign_model' => $field['rmodel'],
                        'foreign_key' => $field['rfield'],
                        'foreign_key_name' => $field['rfieldname'],
                    ];
                }
            }
        }
        // set relation has many
        if (!empty($this->field_defs['relationships'])) {
            foreach ($this->field_defs['relationships'] as $relationship_name => $relationship) {
                $this->relations['has_many'][$relationship_name] = [
                    'local_key' => $relationship['field'],
                    'foreign_module' => $relationship['rmodule'],
                    'foreign_model' => $relationship['rmodel'],
                    'foreign_key' => $relationship['rfield'],
                    'middle_table' => $relationship['middle_table'],
                    'middle_ida' => $relationship['middle_ida'],
                    'middle_idb' => $relationship['middle_idb']
                ];
            }
        }

        // set layout defs
        if (!$this->layout_defs) {
            $path = 'modules/' . $this->module . '/Config/' . $this->getName(true) . '_viewdefs.php';
            if (file_exists(CUSTOMPATH . $path)) {
                $this->layout_defs = include CUSTOMPATH . $path;
            } else if (file_exists(APPPATH . $path)) {
                $this->layout_defs = include APPPATH . $path;
            }
        }

        // load field model
        $this->fieldModel = new AvocaModelField($this);
    }

    public function getName($nonamespace = false)
    {
        if ($nonamespace) {
            $className = basename(str_replace('\\', '/', get_class($this)));
            return $className;
        }

        return get_class($this);
    }

    public function getModule()
    {
        return $this->module;
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
     * @return bool true ==> error
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
     * database fields
     *
     * @return array
     */
    public function getFields()
    {
        if ($this->table) {
            return $this->db->list_fields($this->table);
        }

        return [];
    }

    public function getFieldDefs()
    {
        return $this->field_defs;
    }

    public function getLayoutDefs()
    {
        return $this->layout_defs;
    }

    public function fieldModel()
    {
        return $this->fieldModel;
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
        $fields = $this->getFields();

        $fields_data = [];
        // choose/reformat fields from data
        foreach ($fields as $field) {
            if (!empty($data[$field]) && $field != 'id') {
                $fields_data[$field] = $this->reformat($field, $data[$field]);
            }
        }

        // check empty data
        if (empty($fields_data)) {
            $this->setErrors('Empty data');
            return false;
        }

        // check valid fields
        if (isset($fields['date_created'])) {
            if (!isset($fields_data['date_created']) || !$fields_data['date_created']) {
                $fields_data['date_created'] = date('Y-m-d H:i:s');
            }
        }

        $valid = $this->valid($fields_data);
        if (!empty($valid['error'])) {
            $this->setErrors($valid['message']);
            return false;
        }

        $this->db->set($fields_data);
        $this->db->insert($this->table);
        if ($this->checkErrorDB()) {
            return false;
        }

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
        $fields = $this->getFields();

        $fields_data = [];
        // choose/reformat data fields
        foreach ($fields as $field) {
            if (!empty($data[$field]) && $field != 'id') {
                $fields_data[$field] = $this->reformat($field, $data[$field]);
            }
        }

        // check empty data
        if (empty($fields_data)) {
            $this->setErrors('Empty data');
            return false;
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

            if ($this->checkErrorDB()) {
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
     * @return mixed
     */
    protected function displayRecord($record)
    {
        return $record;
    }

    /**
     * set this->db->limit
     *
     * * @param int $offset
     * @param int $limit
     * @return void
     */
    protected function setDBLimit($offset = 0, $limit = 0)
    {
        if ($limit >= 0) {
            if ($limit > 0) {
                $this->limit = $limit;
            }
        }

        $final_limit = $this->getLimit();
        if ($final_limit > 0) {
            $this->db->limit($this->getLimit(), $offset);
        }
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function getLimit()
    {
        if ($this->limit == 0) {
            $this->limit = config_item('records_per_page');
        }

        return $this->limit;
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
            return $this->displayRecord($query->row_array());
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
     * @param array $orders
     * @return array|mixed|null
     */
    public function get_where($where = '', $row = true, $table = '', $offset = 0, $limit = 0, $orders = [])
    {
        $table = ($table) ? $table : $this->table;
        // count all records
        $total_records = $this->numRows($where, $table);
        // set where
        if (!empty($where)) {
            $this->db->where($where);
        }
        // limit
        $this->setDBLimit($offset, $limit);
        // order by
        foreach ($orders as $order_by => $order_type) {
            $this->db->order_by($order_by, $order_type);
        }
        // query
        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            if ($row) {
                return $this->displayRecord($query->row_array());
            }

            $data = [
                'total' => $total_records,
                'count' => $query->num_rows(),
                'offset' => $offset,
                'per_page' => $this->getLimit(),
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
     * @param array $orders
     * @return array|mixed|null
     */
    public function getAll($table = '', $orders = [])
    {
        return $this->get_where('', false, $table, 0, -1, $orders);
    }

    /**
     * get records with conditions
     *
     * @param string $where
     * @param int $offset
     * @param array $orders
     * @return array|mixed|null
     */
    public function getRecords($where = '', $offset = 0, $orders = [])
    {
        return $this->get_where($where, false, '', $offset, 0, $orders);
    }
}