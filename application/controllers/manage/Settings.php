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

class Settings extends AVC_ManageController
{
    protected $model = 'Setting';

    // ACTION
    public function index()
    {

    }

    /**
     * convert data to ci create table structure
     * @return array
     */
    private function readDatabaseStructure()
    {
        $databases = include APPPATH . 'config/avoca/databases.php';
        $database_structures = [];

        foreach ($databases as $table => $options) {

            $fields = [];
            $indexes = [];

            foreach ($options['fields'] as $fields_option) {

                try {

                    $options_arr = explode(' ', $fields_option);

                    $length = count($options_arr);
                    if ($length < 2) {
                        show_error('ERROR FIELD DATA: ' . $fields_option);
                    }

                    $field = $options_arr[0];
                    $type = $options_arr[1];

                    $fields[$field] = ['type' => $type];

                    if ($length > 2) {
                        $i = 0;
                        foreach ($options_arr as $op) {
                            if ($i == 2) {
                                $fields[$field]['constraint'] = $op;
                            } else if ($i > 2) {
                                if (strpos($op, ':') !== false) {
                                    $arr = explode(':', $op);
                                    $fields[$field][$arr[0]] = $arr[1];
                                }
                            }

                            $i++;
                        }
                    }
                } catch (Exception $exception) {
                    show_error('ERROR DATA!');
                }
            }

            foreach ($options['indexes'] as $index) {
                try {
                    $index_arr = explode(' ', $index);
                    $index_type = $index_arr[0];
                    $index_fields = $index_arr[1];

                    if ($index_type == 'PK') {
                        $indexes['primary'][] = $index_fields;
                    } else {
                        $index_name = $index_arr[2];
                        $indexes['key'][] = [
                            'name' => $index_name,
                            'type' => $index_type,
                            'fields' => $index_fields
                        ];
                    }
                } catch (Exception $exception) {
                    show_error('ERROR DATA INDEX: ' . $index);
                }
            }

            $database_structures[$table] = [
                'name' => $table,
                'ENGINE' => $options['ENGINE'],
                'fields' => $fields,
                'indexes' => $indexes
            ];
        }

        return $database_structures;
    }

    // ACTION - Create/Update database from config/avoca/databases.php
    public function repair_database()
    {
        $this->disableView();
        $this->load->dbforge();

        $db_structure = $this->readDatabaseStructure();

        foreach ($db_structure as $table => $info) {

            if ($this->db->table_exists($table)) {

                $exists_fields = $this->db->list_fields($table);

                foreach ($info['fields'] as $field_name => $field) {
                    if (in_array($field_name, $exists_fields)) {
                        $this->dbforge->modify_column($table, [$field_name => $field]);
                    } else {
                        $this->dbforge->add_column($table, [$field_name => $field]);
                    }
                }

            } else {

                $fields = $info['fields'];
                $this->dbforge->add_field($fields);

                foreach ($info['indexes']['primary'] as $primary) {
                    $this->dbforge->add_key($primary, TRUE);
                }

                $attributes = array('ENGINE' => $info['ENGINE']);
                $this->dbforge->create_table($table, true, $attributes);
            }

            if (!empty($info['indexes']['key'])) {
                foreach ($info['indexes']['key'] as $key) {
                    $this->db->simple_query("
                      DROP INDEX IF EXISTS {$key['name']} ON {$table};
                    ");

                    $this->db->simple_query("
                      ALTER TABLE {$table}
                      ADD {$key['type']} KEY {$key['name']} ({$key['fields']})
                    ");
                }
            }

            if (!empty($info['indexes']['delete'])) {
                foreach ($info['indexes']['delete'] as $key) {
                    $this->db->simple_query("
                      DROP INDEX IF EXISTS {$key['name']} ON {$table};
                    ");
                }
            }
        }

        $this->setSuccess('Repair database success!');
        return $this->redirect('/manage/settings');
    }

    // ACTION
    public function create_module($module_name = null)
    {
        // default value to view
        $modules = include APPPATH . 'config/avoca/modules.php';
        $this->data['module'] = [];
        $this->data['table'] = [];
        $this->data['table_fields'] = '';
        $this->data['table_indexs'] = '';

        if (!empty($modules[$module_name])) {
            $this->data['module'] = $modules[$module_name];

            $tables = include APPPATH . 'config/avoca/databases.php';
            if (!empty($tables[$modules[$module_name]['table']])) {
                $this->data['table'] = $tables[$modules[$module_name]['table']];
                // fields to textarea value
                $this->data['table_fields'] = implode("\n", $this->data['table']['fields']);
                // indexes to textarea value
                $this->data['table_indexes'] = implode("\n", $this->data['table']['indexes']);
            }
        }

        if ($this->isPost()) {

            $this->disableView();

            $model = $this->getPost('model');
            $table = $this->getPost('table');
            $controller = $this->getPost('controller');
            $table_define = $this->getPost('table_define');
            $table_index = $this->getPost('table_index');

            if ($model && $controller) {
                $module = [
                    'controller' => $controller,
                    'model' => $model,
                    'table' => $table,
                ];

                // write to aconfig/voca/modules.php
                $modules[$controller] = $module;
                write_array2file('config/avoca/modules.php', $modules);

                // write to model class in models/
                if ($table) {
                    $this->_createModel($model, $table);
                }

                // write to controller class in controllers/
                $this->_createController($controller, $model);

                // write data base into to config/avoca/databases.php
                if ($table && $table_define) {
                    $this->_createTableDefined($table, $table_define, $table_index);
                }
            }

            $this->setSuccess('Create/update module success!');
            return $this->redirect('/manage/settings');
        }
    }

    private function _createModel($model, $table)
    {
        $model_name = ucfirst(strtolower($model));
        $model_path = APPPATH . 'models/' . $model_name . '.php';

        if (!file_exists($model_path)) {
            $template = file_get_contents(APPPATH . 'config/avoca/builders/model.avc');
            $data = str_replace(
                ['$$MODEL_CLASS$$', '$$TABLE_NAME$$'],
                [$model_name, strtolower($table)],
                $template);

            write_file($model_path, $data, 'w');
        }
    }

    private function _createController($controller, $model)
    {
        $model_name = ucfirst(strtolower($model));
        $controller_name = ucfirst(strtolower($controller));
        $controller_path = APPPATH . 'controllers/admin/' . $controller_name . '.php';

        if (!file_exists($controller_path)) {
            $template = file_get_contents(APPPATH . 'config/avoca/builders/controller.avc');
            $data = str_replace(
                ['$$CONTROLLER_CLASS$$', '$$MODEL_NAME$$'],
                [$controller_name, $model_name],
                $template);

            write_file($controller_path, $data, 'w');
        }
    }

    private function _createTableDefined($table_name, $table_define, $table_index)
    {
        $define = [];
        $define_arr = explode("\n", $table_define);
        foreach ($define_arr as $value) {
            $define[] = trim(trim($value, "\n"));
        }

        $index = [];
        if ($table_index) {
            $index_arr = explode("\n", $table_index);
            foreach ($index_arr as $value) {
                $index[] = trim(trim($value, "\n"));
            }
        }

        $table = [
            'name' => strtolower($table_name),
            'ENGINE' => 'InnoDB',
            'fields' => $define,
            'indexes' => $index
        ];

        $tables = include APPPATH . 'config/avoca/databases.php';
        $tables[$table_name] = $table;

        write_array2file('config/avoca/databases.php', $tables);
    }

    // ACTION
    public function modules()
    {
        $modules = include APPPATH . 'config/avoca/modules.php';
        $this->data['modules'] = $modules;
    }
}