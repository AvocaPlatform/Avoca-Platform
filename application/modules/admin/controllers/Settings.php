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

class Settings extends AVC_AdminController
{
    protected $model = 'setting';

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
        $databases = include APPPATH . 'modules/admin/config/databases.php';
        $database_structures = [];

        foreach ($databases as $table => $options) {

            $fields = [];
            $indexes = [];

            foreach ($options['fields'] as $fields_option) {

                try {

                    $fields_option = preg_replace('/\s+/', ' ', $fields_option);
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

                                    $value = strtolower(trim($arr[1]));

                                    if ($value === 'true') {
                                        $value = true;
                                    } else if ($value === 'false') {
                                        $value = false;
                                    }

                                    $fields[$field][$arr[0]] = $value;
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

    // ACTION - Create/Update database from modules/admin/config/databases.php
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
        return $this->admin_redirect('/settings');
    }

    // ACTION
    public function create_module($module_name = null)
    {
        // default value to view
        $modules = include APPPATH . 'modules/admin/config/modules.php';
        $this->data['module'] = [];
        $this->data['table'] = [];
        $this->data['table_fields'] = '';
        $this->data['table_indexs'] = '';

        if (!empty($modules[$module_name])) {
            $this->data['module'] = $modules[$module_name];

            $tables = include APPPATH . 'modules/admin/config/databases.php';
            if (!empty($tables[$modules[$module_name]['table']])) {
                $this->data['table'] = $tables[$modules[$module_name]['table']];
                // fields to textarea value
                $this->data['table_fields'] = implode("\n", $this->data['table']['fields']);
                // indexes to textarea value
                $this->data['table_indexes'] = implode("\n", $this->data['table']['indexes']);
            }
        }

        $this->addJs([
            avoca_static(false) . '/themes/avoca_global/js/jquery-sortable.js',
        ]);

        // process create module
        if ($this->isPost()) {

            $this->disableView();
            $this->load->helper('file');

            $module_name = $controller = $this->getPost('module');
            $model = $this->getPost('model') ? $this->getPost('model') : $module_name;
            $table = $this->getPost('table') ? $this->getPost('table') : $module_name;

            if ($module_name) {
                $module = [
                    'module' => $module_name,
                    'controller' => $module_name,
                    'model' => $model,
                    'table' => $table,
                ];

                // write to modules/admin/config/modules.php
                $modules[$controller] = $module;
                write_array2file('modules/admin/config/modules.php', $modules);

                $package_folder = APPPATH . 'modules/admin/config/module_builders/' . $module_name;
                if (!is_dir($package_folder)) {
                    mkdir($package_folder, 0775, true);
                }

                $var_defs = [];
                $var_defs_file = $package_folder . '/vardefs.php';
                if (file_exists($var_defs_file)) {
                    $var_defs = include $var_defs_file;
                }

                $var_defs['module'] = $module_name;
                $var_defs['model'] = $model;
                $var_defs['table'] = $table;

                if (empty($var_defs['fields'])) {
                    $var_defs['fields'] = [
                        'id INT 10 unsigned:true auto_increment:true',
                        'date_created DATETIME',
                    ];
                }

                write_array2file('modules/admin/config/module_builders/' . $module_name . '/vardefs.php', $var_defs);
                return $this->jsonData([]);
            }
        }
    }

    private function _createModel($model, $table, $folder = 'custom')
    {
        $model_name = ucfirst(strtolower($model));
        if ($folder == 'core') {
            $model_path = APPPATH . 'models/' . $model_name . '.php';
        } else {
            $model_path = CUSTOMPATH . 'models/' . $model_name . '.php';
        }

        if (!file_exists($model_path)) {
            $template = file_get_contents(APPPATH . 'modules/admin/config/builders/model.avc');
            $data = str_replace(
                ['$$MODEL_CLASS$$', '$$TABLE_NAME$$'],
                [$model_name, strtolower($table)],
                $template);

            write_file($model_path, $data, 'w');
        }
    }

    private function _createController($controller, $model, $folder = 'custom')
    {
        $model_name = ucfirst(strtolower($model));
        $controller_name = ucfirst(strtolower($controller));
        if ($folder == 'core') {
            $controller_path = APPPATH . 'controllers/manage/' . $controller_name . '.php';
        } else {
            $controller_path = CUSTOMPATH . 'controllers/manage/' . $controller_name . '.php';
        }

        if (!file_exists($controller_path)) {
            $template = file_get_contents(APPPATH . 'modules/admin/config/builders/controller.avc');
            $data = str_replace(
                ['$$CONTROLLER_CLASS$$', '$$MODEL_NAME$$'],
                [$controller_name, strtolower($model_name)],
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

        $tables = include APPPATH . 'modules/admin/config/databases.php';
        $tables[$table_name] = $table;

        write_array2file('modules/admin/config/databases.php', $tables);
    }

    // ACTION
    public function modules()
    {
        $modules = include APPPATH . 'modules/admin/config/modules.php';
        $this->data['modules'] = $modules;
    }

    // ACTION
    public function mail_setting()
    {
        if ($this->isPost()) {
            $mail_from = $this->getPost('mail_from');

            $mail = $this->getPost('mail');
            $mail['protocol'] = 'smtp';
            $mail['wordwrap'] = true;
            if(empty($mail['charset'])) {
                $mail['charset'] = 'utf-8';
            }

            $handle = fopen(APPPATH . 'config/mail.php', 'w');
            fwrite($handle, "<?php\n\n");
            fwrite($handle, "\$config['mail_from'] = '$mail_from';\n");
            fwrite($handle, "\$config['mail'] = " . var_export($mail, true) . ";\n");
            fclose($handle);

            $this->setSuccess('Save mail setting successful!');
            return $this->admin_redirect('/settings/mail_setting');
        }

        $this->load->config('mail');
        $this->data['mail'] = config_item('mail');
        $this->data['mail_from'] = config_item('mail_from');
    }
}