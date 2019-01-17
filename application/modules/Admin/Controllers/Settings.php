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

use Avoca\Controllers\AvocaAdminController;

class Settings extends AvocaAdminController
{
    protected $model = 'Admin/Setting';

    // ACTION
    public function index()
    {

    }

    // ACTION - Create/Update database from modules/Admin/Config/databases.php
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
        return $this->admin_redirect('/Settings');
    }

    // ACTION
    public function create_module($module_name = null)
    {
        /** @var Setting $settingModel */
        $settingModel = $this->getModel('Admin/Setting');
        $modules = $settingModel->getModules(true);

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

                // write to modules/Admin/Config/modules.php
                $modules[$controller] = $module;
                write_array2file('modules/Admin/Config/modules.php', $modules);

                $package_folder = APPPATH . 'modules/Admin/Config/module_builders/' . $module_name;
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

                write_array2file('modules/Admin/Config/module_builders/' . $module_name . '/vardefs.php', $var_defs);
                return $this->admin_redirect('/Settings/create_module/' . $module_name . '?tab=ModuleFieldInfo');
            }
        }

        $this->setTitle('Create Module', true);
        $this->addJs([
            'https://cdn.jsdelivr.net/npm/lodash@4.17.11/lodash.min.js',
            'https://cdn.jsdelivr.net/npm/vue',
            'https://cdn.jsdelivr.net/npm/vue-resource@1.5.1',
            'https://cdn.jsdelivr.net/npm/sortablejs@1.7.0/Sortable.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.17.0/vuedraggable.min.js',
            'https://unpkg.com/vue-drag-drop',
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

        // default value to view
        $this->data['tab'] = $tab;
        $this->data['module'] = [
            'relationships' => new stdClass(),
            'indexes' => new stdClass()
        ];
        $this->data['create_module'] = true;
        $this->data['module_created'] = 0;
        $this->data['all_fields'] = [];
        $this->data['viewdefs'] = new stdClass();

        if ($module_name) {
            // check exist module
            if (!isset($modules[$module_name])) {
                $this->setError('Did not found this module');
                return $this->admin_redirect('/settings/modules');
            }

            // get module defined
            $this->data['all_fields'] = $settingModel->getModuleFields($module_name, true, true);
            $this->data['create_module'] = false;
            if (is_dir(APPPATH . 'modules/' . $module_name)) {
                $this->data['module_created'] = 1;
                $module = include APPPATH . 'modules/' . $module_name . '/Config/' . $modules[$module_name]['model'] . '_vardefs.php';
                $viewdefs = include APPPATH . 'modules/' . $module_name . '/Config/' . $modules[$module_name]['model'] . '_viewdefs.php';
            } else {
                $this->data['module_created'] = 0;
                $module = include APPPATH . 'modules/Admin/Config/module_builders/' . $module_name . '/vardefs.php';
                $viewdefs = include APPPATH . 'modules/Admin/Config/module_builders/' . $module_name . '/viewdefs.php';
            }

            // clean up module defined
            if (!isset($module['relationships'])) {
                $module['relationships'] = new stdClass();
            }

            if (!isset($module['indexes'])) {
                $module['indexes'] = new stdClass();
            }

            /** @var Setting $settingModel */
            $settingModel = $this->getModel('Admin/Setting');
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
            $this->data['viewdefs'] = $viewdefs;
        }

        // field types
        $dbTypes = include APPPATH . 'modules/Admin/Config/database_types.php';
        $types = [];
        foreach ($dbTypes['defined'] as $type => $type_value) {
            $types[] = $type;
        }

        // app_list_strings
        $this->data['app_list_strings'] = getAppListStrings(null, true);
        $this->data['types'] = array_merge($types, $dbTypes['default']);
        $this->data['allModules'] = $settingModel->getModules();
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

        // module defined
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
                        . '_' . $relationship['field'] . '_' . $relationship['rfield'];
                    unset($relationship['fields']);
                    $relationships[$relate_name] = $relationship;
                }
            }
        }

        $data['relationships'] = $relationships;

        // layout defined
        $viewdefs = [];
        if (file_exists(APPPATH . 'modules/' . $data['module'] . '/Config/' . $data['model'] . '_viewdefs.php')) {
            $viewdefs = include APPPATH . 'modules/' . $data['module'] . '/Config/' . $data['model'] . '_viewdefs.php';
        } else if (file_exists(APPPATH . 'modules/Admin/Config/module_builders/' . $data['module'] . '/viewdefs.php')) {
            $viewdefs = include APPPATH . 'modules/Admin/Config/module_builders/' . $data['module'] . '/viewdefs.php';
        }

        $list = [];
        $list_view = $this->getPost('list_view');
        foreach ($list_view as $field) {
            if (!empty($field['name'])) {
                $list[$field['name']] = $field;
            }
        }

        $record = [];
        $record_view = $this->getPost('record_view');
        $i = 0;
        foreach ($record_view as $line) {
            foreach ($line as $field) {
                if (!empty($field['name'])) {
                    $record[$i][$field['name']] = $field;
                }
            }
            $i++;
        }

        $viewdefs['title'] = $this->getPost('field_title');
        $viewdefs['list']['fields'] = $list;
        $viewdefs['record']['fields'] = $record;

        // write defined module
        if (file_exists(APPPATH . 'modules/' . $data['module'] . '/Config/' . $data['model'] . '_vardefs.php')) {
            write_array2file('modules/' . $data['module'] . '/Config/' . $data['model'] . '_vardefs.php', $data);
            write_array2file('modules/' . $data['module'] . '/Config/' . $data['model'] . '_viewdefs.php', $viewdefs);
        } else if (file_exists(APPPATH . 'modules/Admin/Config/module_builders/' . $data['module'] . '/vardefs.php')) {
            write_array2file('modules/Admin/Config/module_builders/' . $data['module'] . '/vardefs.php', $data);
            write_array2file('modules/Admin/Config/module_builders/' . $data['module'] . '/viewdefs.php', $viewdefs);
        }

        return $this->jsonData([
            'error' => 0
        ]);
    }

    // ACTION AJAX
    public function deploy_module()
    {
        $this->disableView();

        if (!$this->isPost()) {
            return $this->jsonData(['error' => 1]);
        }

        $module = $this->getPost('module');
        if (!$module) {
            return $this->jsonData(['error' => 1]);
        }

        // check source
        $vardefs_source = APPPATH . "modules/Admin/Config/module_builders/{$module}/vardefs.php";
        $viewdefs_source = APPPATH . "modules/Admin/Config/module_builders/{$module}/viewdefs.php";
        if (!file_exists($vardefs_source)) {
            return $this->jsonData(['error' => 1]);
        }

        // check params
        $vardefs = include $vardefs_source;
        if (empty($vardefs['model'])) {
            return $this->jsonData([
                'error' => 1,
                'message' => 'Invalid model name',
            ]);
        }

        /** @var Setting $settingModel */
        $settingModel = $this->getModel('Admin/Setting');
        $settingModel->createModule($module);

        // create vardefs file
        write_array2file(APPPATH . "modules/$module/Config/{$vardefs['model']}_vardefs.php", $vardefs);
        // create view source
        if (file_exists($viewdefs_source)) {
            $viewdefs = include $viewdefs_source;
            write_array2file(APPPATH . "modules/$module/Config/{$vardefs['model']}_viewdefs.php", $viewdefs);
        }

        // create required files
        $controller = !empty($vardefs['controller']) ? $vardefs['controller'] : $module;
        $model = !empty($vardefs['model']) ? $vardefs['model'] : '';
        $table = !empty($vardefs['table']) ? $vardefs['table'] : '';

        $settingModel->createController($module, $controller, $model);
        if ($model) {
            $table = $table ? $table : $module;
            $settingModel->createModel($module, $model, $table);
            $settingModel->createAPIController($module, $controller, $model);
        }

        return $this->jsonData(['error' => 0]);
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
        $settingModel = $this->getModel('Admin/Setting');
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
        $settingModel = $this->getModel('Admin/Setting');
        $modules = $settingModel->getModules();
        $this->data['modules'] = $modules;

//        $this->disableView();
//        echo '<pre>';
//        print_r($settingModel->transferVardefs2DBConfig(APPPATH . 'modules/Admin/Config/module_builders/usergroups/vardefs.php'));
    }

    public function remove_module($module)
    {
        $this->disableView();

        // not allow module deployed
        if (is_dir(APPPATH . "modules/{$module}")) {
            $this->setError('Can not delete module deployed');
            return $this->admin_redirect('/Settings/modules');
        }

        /** @var Setting $settingModel */
        $settingModel = $this->getModel('Admin/Setting');

        // get all modules
        $allModules = $settingModel->getModules(true);
        if (!empty($allModules[$module])) {
            unset($allModules[$module]);
            write_array2file('modules/Admin/Config/modules.php', $allModules);
        }

        if (is_dir(APPPATH . "modules/Admin/Config/module_builders/$module")) {
            delete_files(APPPATH . "modules/Admin/Config/module_builders/$module", true);
            rmdir(APPPATH . "modules/Admin/Config/module_builders/$module");
        }

        $this->setSuccess("Remove module: {$module} successful");
        return $this->admin_redirect('/Settings/modules');
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
            return $this->admin_redirect('/Settings/mail_setting');
        }

        $this->load->config('mail');
        $this->data['mail'] = config_item('mail');
        $this->data['mail_from'] = config_item('mail_from');
    }
}