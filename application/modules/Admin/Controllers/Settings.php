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


namespace App\Modules\Admin\Controllers;


use App\Modules\Admin\Models\Setting;
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
        // process create module
        if ($this->isPost()) {
            $this->disableView();
            return $this->_createDraftModule();
        }

        /** @var Setting $settingModel */
        $settingModel = $this->getModel('Admin/Setting');
        $modules = $settingModel->getModules(true);

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
            'relationships' => new \stdClass(),
            'indexes' => new \stdClass()
        ];
        $this->data['create_module'] = true;
        $this->data['module_created'] = 0;
        $this->data['all_fields'] = [];
        $this->data['viewdefs'] = new \stdClass();

        if ($module_name) {
            // check exist module
            if (!isset($modules[$module_name])) {
                $this->setError('Did not found this module');
                return $this->admin_redirect('/Settings/modules');
            }

            $model_name = $modules[$module_name]['model'];
            // get module defined
            $this->data['create_module'] = false;
            $this->data['module_created'] = $settingModel->isCreatedModule($module_name) ? 1 : 0;
            $this->data['all_fields'] = $settingModel->getModuleFields($module_name, true, true);

            $module = $settingModel->getModuleVarDefs($module_name, $model_name);
            $viewdefs = $settingModel->getModuleViewDefs($module_name, $model_name);

            // clean up module defined
            if (!isset($module['relationships'])) {
                $module['relationships'] = new \stdClass();
            }

            if (!isset($module['indexes'])) {
                $module['indexes'] = new \stdClass();
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
            if ($viewdefs) {
                $this->data['viewdefs'] = $viewdefs;
            }
        }

        // field types
        $dbTypes = $settingModel->getConfig('database_types.php');
        $types = [];
        foreach ($dbTypes['defined'] as $type => $type_value) {
            $types[] = $type;
        }

        // app_list_strings
        $this->data['app_list_strings'] = getAppListStrings(null, true);
        $this->data['types'] = array_merge($types, $dbTypes['default']);
        $this->data['allModules'] = $settingModel->getModules();
    }

    // ACTION
    private function _createDraftModule()
    {
        $this->load->helper('file');
        /** @var Setting $settingModel */
        $settingModel = $this->getModel('Admin/Setting');
        $modules = $settingModel->getModules(true);

        $module_name = $controller = $this->getPost('module');
        $model = $this->getPost('model') ? $this->getPost('model') : $module_name;
        $table = $this->getPost('table') ? $this->getPost('table') : $module_name;

        if ($module_name) {
            $module = [
                'module' => $module_name,
                'model' => $model,
            ];

            // write to custom/modules/Admin/Config/modules.php
            $modules[$controller] = $module;
            $settingModel->writeConfig('modules.php', $modules);

            $var_defs = $settingModel->getModuleVarDefs($module_name, $model);
            $var_defs['module'] = $module_name;
            $var_defs['model'] = $model;
            $var_defs['table'] = $table;

            if (empty($var_defs['fields'])) {
                $var_defs['fields'] = [
                    'id' => [
                        'name' => 'id',
                        'label' => 'Id',
                        'type' => 'id',
                    ],
                    'date_created' => [
                        'name' => 'date_created',
                        'label' => 'Date created',
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

            // module not created
            if (!is_dir(CUSTOMPATH . "modules/{$module_name}")
                && !is_dir(APPPATH . "modules/{$module_name}")) {
                if (!is_dir(CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module_name}")) {
                    mkdir(CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module_name}", 0775, true);
                }
                $settingModel->writeConfig('ModuleBuilders/' . $module_name . '/vardefs.php', $var_defs);
            } else {
                if (!is_dir(CUSTOMPATH . "modules/{$module_name}/Config")) {
                    mkdir(CUSTOMPATH . "modules/{$module_name}/Config", 0775, true);
                }
                $settingModel->writeConfig("modules/{$module_name}/Config/{$model}_vardefs.php", $var_defs, false);
            }

            return $this->admin_redirect('/Settings/create_module/' . $module_name . '?tab=ModuleFieldInfo');
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

        /** @var Setting $settingModel */
        $settingModel = $this->getModel('Admin/Setting');
        // layout defined
        $viewdefs = $settingModel->getModuleViewDefs($data['module'], $data['model']);

        $list = [];
        $list_view = $this->getPost('list_view');
        foreach ($list_view as $field) {
            if (!empty($field['name'])) {
                unset($field['label']);
                $list[$field['name']] = $field;
            }
        }

        $record = [];
        $record_view = $this->getPost('record_view');
        $i = 0;
        foreach ($record_view as $line) {
            foreach ($line as $field) {
                if (!empty($field['name'])) {
                    unset($field['label']);
                    $record[$i][$field['name']] = $field;
                }
            }
            $i++;
        }

        $viewdefs['title'] = $this->getPost('field_title');
        $viewdefs['list']['fields'] = $list;
        $viewdefs['record']['fields'] = $record;

        // write defined module
        // module draft
        if (!is_dir(CUSTOMPATH . "modules/{$data['module']}")
            && !is_dir(APPPATH . "modules/{$data['module']}")) {
            $settingModel->writeConfig("ModuleBuilders/{$data['module']}/vardefs.php", $data);
            $settingModel->writeConfig("ModuleBuilders/{$data['module']}/viewdefs.php", $viewdefs);
        } else {
            // module created
            $settingModel->writeConfig("modules/{$data['module']}/Config/{$data['model']}_vardefs.php", $data, false);
            $settingModel->writeConfig("modules/{$data['module']}/Config/{$data['model']}_viewdefs.php", $viewdefs, false);
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
        $vardefs_source = CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/vardefs.php";
        $viewdefs_source = CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/viewdefs.php";
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
        $settingModel->writeConfig("modules/$module/Config/{$vardefs['model']}_vardefs.php", $vardefs, false);
        // create view source
        if (file_exists($viewdefs_source)) {
            $viewdefs = include $viewdefs_source;
            $settingModel->writeConfig("modules/$module/Config/{$vardefs['model']}_viewdefs.php", $viewdefs, false);
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