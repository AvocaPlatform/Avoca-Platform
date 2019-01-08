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
    protected $model = 'admin/setting';

    // ACTION
    public function index()
    {

    }

    // ACTION - Create/Update database from modules/admin/config/databases.php
    public function repair_database()
    {
        $this->disableView();
        $this->load->dbforge();

        /** @var Setting $settingModel */
        $settingModel = $this->getModel($this->model);
        $db_structure = $settingModel->readDatabaseStructure();
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
        $this->setTitle('Create Module', true);

        $this->addJs([
            avoca_static(false) . '/themes/avoca_global/js/jquery-sortable.js',
            'https://cdn.jsdelivr.net/npm/vue',
            'https://cdn.jsdelivr.net/npm/vue-resource@1.5.1',
        ]);

        $tab = $this->getQuery('tab');
        if (!in_array($tab, [
            'ModuleInfo',
            'ModuleFieldInfo',
            'ModuleRelateInfo',
            'ModuleListView',
            'ModuleRecordView'
        ])) {
            $tab = 'ModuleInfo';
        }

        $this->data['tab'] = $tab;

        /** @var Setting $settingModel */
        $settingModel = $this->getModel('admin/setting');
        $modules = $settingModel->getModules(true);

        // default value to view
        $this->data['module'] = [];
        $this->data['create_module'] = true;
        $this->data['module_created'] = 0;

        if ($module_name) {
            // check exist module
            if (!isset($modules[$module_name])) {
                $this->setError('Did not found this module');
                return $this->admin_redirect('/settings/modules');
            }

            $this->data['create_module'] = false;
            if (is_dir(APPPATH . 'modules/' . $module_name)) {
                $this->data['module_created'] = 1;
                $module = include APPPATH . 'modules/' . $module_name . '/config/' . $modules[$module_name]['model'] . '_vardefs.php';
            } else {
                $this->data['module_created'] = 0;
                $module = include APPPATH . 'modules/admin/config/module_builders/' . $module_name . '/vardefs.php';
            }

            // default value
            if (!isset($module['relationships'])) {
                $module['relationships'] = [];
            }

            if (!isset($module['indexes'])) {
                $module['indexes'] = [];
            }

            /** @var Setting $settingModel */
            $settingModel = $this->getModel('admin/setting');
            $relationships = [];
            // clean up relationship data
            foreach ($module['relationships'] as $relationship) {
                if($relationship['module']) {
                    $relationship['fields'] = $settingModel->getModuleFields($relationship['module']);
                } else {
                    $relationship['fields'] = [];
                }

                $relationships[] = $relationship;
            }

            $module['relationships'] = $relationships;
            $this->data['module'] = $module;
        }

        // field types
        $dbTypes = include APPPATH . 'modules/admin/config/database_types.php';
        $types = [];
        foreach ($dbTypes['defined'] as $type => $type_value) {
            $types[] = $type;
        }

        // app_list_strings
        $this->data['app_list_strings'] = getAppListStrings(null, true);
        $this->data['types'] = array_merge($types, $dbTypes['default']);
        $this->data['allModules'] = $settingModel->getModules();

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
                    'model' => $model,
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
                        'id' => [
                            'name' => 'id',
                            'type' => 'id',
                        ],
                        'date_created' => [
                            'name' => 'date_created',
                            'type' => 'datetime',
                        ],
                    ];
                }

                if (empty($var_defs['indexes'])) {
                    $var_defs['indexes'] = [
                        'primary' => [
                            'type' => 'PK',
                            'fields' => ['id'],
                        ],
                    ];
                }

                write_array2file('modules/admin/config/module_builders/' . $module_name . '/vardefs.php', $var_defs);
                return $this->admin_redirect('/settings/create_module/' . $module_name . '?tab=ModuleFieldInfo');
            }
        }
    }

    // ACTION AJAX
    public function build_module()
    {
        $this->disableView();

        if (!$this->isPost()) {
            return $this->jsonData([
                'error' => 1
            ]);
        }

        $data = $this->getPost('data');
        if (!$data) {
            return $this->jsonData([
                'error' => 1
            ]);
        }

        if (empty($data['module'])
            || empty($data['model'])
            || empty($data['fields'])) {
            return $this->jsonData([
                'error' => 1
            ]);
        }

        if (empty($data['table'])) {
            $data['table'] = $data['module'];
        }

        // reformat relationships
        $relationships = [];
        if (!empty($data['relationships'])) {
            foreach ($data['relationships'] as $relationship) {
                if (!empty($relationship['field'])
                    && !empty($relationship['module'])
                    && !empty($relationship['rfield'])) {
                    $relate_name = $data['module'] . '_' . $relationship['module']
                        . $relationship['field'] . '_' . $relationship['rfield'];
                    $relationships[$relate_name] = $relationship;
                }
            }
        }

        if (file_exists(APPPATH . 'modules/' . $data['module'] . '/config/' . $data['model'] . '_vardefs.php')) {
            write_array2file('modules/' . $data['module'] . '/config/' . $data['model'] . '_vardefs.php', $data);
        } else if (file_exists(APPPATH . 'modules/admin/config/module_builders/' . $data['module'] . '/vardefs.php')) {
            write_array2file('modules/admin/config/module_builders/' . $data['module'] . '/vardefs.php', $data);
        }

        return $this->jsonData([
            'error' => 0
        ]);
    }

    // ACTION AJAX
    public function deploy_module()
    {

    }

    // ACTION AJAX
    public function edit_list_strings($string = null)
    {
        $this->data['string'] = '';
        $this->data['list_strings'] = [];

        if ($string) {
            $this->data['string'] = $string;

            $list_strings = getAppListStrings($string);
            $this->data['list_strings'] = $list_strings;
        }

        if ($this->getQuery('ajax') == 1) {
            return $this->jsonData();
        }
    }

    // ACTION AJAX
    public function save_list_strings()
    {
        $this->disableView();
        if ($this->isPost()) {
            if ($this->getPost('name')) {
                $name = $this->getPost('name');
                $value = $this->getPost('value') ?: [];

                $app_list_strings = [];
                if (file_exists(CUSTOMPATH . 'config/app_list_strings.php')) {
                    $app_list_strings = include CUSTOMPATH . 'config/app_list_strings.php';
                }

                $app_list_strings[$name] = $value;
                write_array2file(CUSTOMPATH . 'config/app_list_strings.php', $app_list_strings, false);

                return $this->jsonData([
                    'error' => 0
                ]);
            }
        }
    }

    // ACTION AJAX
    public function module_fields($module)
    {
        $this->disableView();

        /** @var Setting $settingModel */
        $settingModel = $this->getModel('admin/setting');
        $fields = $settingModel->getModuleFields($module);

        if (empty($fields)) {
            return $this->jsonData([
                'error' => 1
            ]);
        }

        return $this->jsonData([
            'error' => 0,
            'fields' => $fields
        ]);
    }

    // ACTION
    public function modules()
    {
        /** @var Setting $settingModel */
        $settingModel = $this->getModel('admin/setting');
        $modules = $settingModel->getModules();
        $this->data['modules'] = $modules;

//        $this->disableView();
//        echo '<pre>';
//        print_r($settingModel->transferVardefs2DBConfig(APPPATH . 'modules/users/config/user_vardefs.php'));
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