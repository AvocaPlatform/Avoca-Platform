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


namespace App\Modules\Admin\Models;


use Avoca\Models\AvocaModel;

class Setting extends AvocaModel
{
    protected $table = 'settings';

    private $dbParams = ['unsigned', 'auto_increment', 'unique', 'null', 'default'];

    public function readDatabaseStructure($databases = [])
    {
        if (empty($databases)) {
            $databases = include APPPATH . 'modules/Admin/Config/databases.php';
        }

        $database_structures = [];
        foreach ($databases as $table => $options) {

            $fields = $this->transferDBFields($options['fields']);
            $indexes = $this->transferDBIndexes($options['indexes']);

            $database_structures[$table] = [
                'name' => $table,
                'ENGINE' => $options['ENGINE'],
                'fields' => $fields,
                'indexes' => $indexes
            ];
        }

        return $database_structures;
    }

    public function transferDBFields($field_raw)
    {
        $fields = [];

        foreach ($field_raw as $fields_option) {
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

        return $fields;
    }

    public function transferDBIndexes($index_raw)
    {
        $indexes = [];

        foreach ($index_raw as $index) {
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

        return $indexes;
    }

    public function transferVardefs2DBConfig($vardef_path)
    {
        if (!file_exists($vardef_path)) {
            return false;
        }

        $vardefs = include $vardef_path;
        $db_types = include APPPATH . 'modules/Admin/Config/database_types.php';
        $defined_types = $db_types['defined'];

        $fields = [];
        if (!empty($vardefs['fields'])) {
            foreach ($vardefs['fields'] as $field => $options) {
                $type = isset($options['type']) ? $options['type'] : 'VARCHAR';
                $type_key = strtolower($type);

                if (!empty($defined_types[$type_key])) {
                    $fvar = sprintf($defined_types[$type_key], $field);
                } else {
                    $constraint = isset($options['constraint']) ? $options['constraint'] : '';
                    if (!empty($options['decimal'])) {
                        $constraint .= ',' . $options['decimal'];
                    }

                    $fvar = "$field $type $constraint";
                    $fvar = trim($fvar) . ' ';

                    foreach ($options as $key => $value) {
                        $key = strtolower($key);
                        if (in_array($key, $this->dbParams)) {
                            $fvar .= $key . ':' . $value . ' ';
                        }
                    }

                    $fvar = trim($fvar);
                }

                $fields[$field] = $fvar;
            }
        }

        $indexes = [];
        if (!empty($vardefs['indexes'])) {
            foreach ($vardefs['indexes'] as $index_name => $index_opt) {
                if ($index_opt['type'] == 'PK') {
                    $index = 'PK ' . implode(',', $index_opt['fields']);
                } else {
                    $index = $index_opt['type'] . ' ' . implode(',', $index_opt['fields']) . ' ' . $index_name;
                }

                $indexes[] = $index;
            }
        }

        return [
            'fields' => $fields,
            'indexes' => $indexes,
        ];
    }

    public function getModules($raw = false)
    {
        $modules = include APPPATH . 'modules/Admin/Config/modules.php';
        if ($raw) {
            return $modules;
        }

        $allModules = [];
        foreach ($modules as $module) {
            $is_created = true;
            if (!is_dir(APPPATH . 'modules/' . $module['module'])) {
                $is_created = false;
            }

            $allModules[$module['module']] = [
                'module' => $module['module'],
                'model' => $module['model'],
                'is_created' => $is_created,
            ];
        }

        return $allModules;
    }

    public function isCreatedModule($module)
    {
        if (is_dir(CUSTOMPATH . "modules/{$module}")) {
            return true;
        }

        if (is_dir(APPPATH . "modules/{$module}")) {
            return true;
        }

        return false;
    }

    public function getModuleFields($module, $only_field_name = true, $include_label = false) {
        $modules = $this->getModules();
        if (empty($modules[$module])) {
            return [];
        }

        $module_info = $modules[$module];
        if ($module_info['is_created']) {
            if (file_exists(CUSTOMPATH . "modules/{$module}/Config/{$module_info['model']}_vardefs.php")) {
                $vardefs = include CUSTOMPATH . "modules/{$module}/Config/{$module_info['model']}_vardefs.php";
            } else if (file_exists(APPPATH . "modules/{$module}/Config/{$module_info['model']}_vardefs.php")) {
                $vardefs = include APPPATH . "modules/{$module}/Config/{$module_info['model']}_vardefs.php";
            } else {
                return [];
            }
        } else if (file_exists(CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/vardefs.php")) {
            $vardefs = include CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/vardefs.php";
        } else {
            return [];
        }

        if (!$only_field_name) {
            return $vardefs['fields'];
        }

        $fields = [];
        if (!$include_label) {
            foreach ($vardefs['fields'] as $field => $options) {
                $fields[] = $field;
            }
        } else {
            foreach ($vardefs['fields'] as $field => $options) {
                $label = !empty($options['label']) ? $options['label'] : __(str_replace('_', ' ', ucfirst($field)));
                $fields[] = [
                    'name' => $field,
                    'label' => $label,
                ];
            }
        }

        return $fields;
    }

    public function getModuleVarDefs($module, $model = '')
    {
        $varDefs = null;
        if ($model) {
            $varDefs = $viewDefs = get_file_array("modules/{$module}/Config/{$model}_vardefs.php", null);
        }

        if (!$varDefs) {
            if (file_exists(CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/vardefs.php")) {
                $varDefs = include CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/vardefs.php";
            }
        }

        return $varDefs;
    }

    public function getModuleViewDefs($module, $model = '')
    {
        $viewDefs = null;
        if ($model) {
            $viewDefs = get_file_array("modules/{$module}/Config/{$model}_viewdefs.php", null);
        }

        if (!$viewDefs) {
            if (file_exists(CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/viewdefs.php")) {
                $viewDefs = include CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/viewdefs.php";
            }
        }

        return $viewDefs;
    }

    public function getConfig($uri)
    {
        $path = 'modules/Admin/Config/' . $uri;
        return get_file_array($path);
    }

    public function writeConfig($uri, array $data, $admin_folder = true)
    {
        if ($admin_folder) {
            $path = 'modules/Admin/Config/' . $uri;
        } else {
            $path = $uri;
        }

        write_array2file($path, $data, 1);
    }

    public function createModel($module, $model, $table)
    {
        $model_name = $model;
        $model_path = CUSTOMPATH . 'modules/' . $module . '/Models/' . $model_name . '.php';

        if (!file_exists($model_path)) {
            $template = file_get_contents(APPPATH . 'modules/Admin/Config/builders/model.avc');
            $data = str_replace(
                ['$$MODULE$$', '$$MODEL_CLASS$$', '$$TABLE_NAME$$'],
                [$module, $model_name, strtolower($table)],
                $template);

            write_file($model_path, $data, 'w');
        }
    }

    public function createController($module, $controller, $model)
    {
        $model_name = $model;
        $controller_name = $controller;
        $controller_path = CUSTOMPATH . 'modules/' . $module . '/Controllers/' . $controller_name . '.php';

        if (!file_exists($controller_path)) {
            $template = file_get_contents(APPPATH . 'modules/Admin/Config/builders/controller.avc');
            $data = str_replace(
                ['$$MODULE$$', '$$CONTROLLER_CLASS$$', '$$MODEL_NAME$$'],
                [$module, $controller_name, $model_name],
                $template);

            write_file($controller_path, $data, 'w');
        }
    }

    public function createAPIController($module, $controller, $model, $version = 1)
    {
        $model_name = "$module/$model";
        $controller_name = $controller;
        $controller_path = CUSTOMPATH . "modules/$module/Controllers/ApiV{$version}.php";

        if (!file_exists($controller_path)) {
            $template = file_get_contents(APPPATH . 'modules/Admin/Config/builders/api_controller.avc');
            $data = str_replace(
                ['$$MODULE$$', '$$CONTROLLER_CLASS$$', '$$MODEL_NAME$$'],
                [$module, $controller_name, $model_name],
                $template);

            write_file($controller_path, $data, 'w');
        }
    }

    public function createTableDefined($table_name, $table_define, $table_index)
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

        $tables = include APPPATH . 'modules/Admin/Config/databases.php';
        $tables[$table_name] = $table;

        $this->writeConfig('modules/Admin/Config/databases.php', $tables, false);
    }

    public function createModule($module)
    {
        if (!file_exists(CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/vardefs.php")) {
            return false;
        }

        $controller = $module;
        $controller_path = CUSTOMPATH . "modules/{$module}/Controllers/{$controller}.php";

        // not yet create module
        if (!file_exists($controller_path)) {
            // create module dir
            if (!is_dir(CUSTOMPATH . "modules{$module}")) {
                mkdir(CUSTOMPATH . "modules/{$module}", 0775);
            }

            if (!is_dir(CUSTOMPATH . 'modules/' . $module . '/Controllers')) {
                mkdir(CUSTOMPATH . 'modules/' . $module . '/Controllers', 0775);
            }

            if (!is_dir(CUSTOMPATH . 'modules/' . $module . '/Models')) {
                mkdir(CUSTOMPATH . 'modules/' . $module . '/Models', 0775);
            }

            if (!is_dir(CUSTOMPATH . 'modules/' . $module . '/Config')) {
                mkdir(CUSTOMPATH . 'modules/' . $module . '/Config', 0775);
            }

            if (!is_dir(CUSTOMPATH . 'modules/' . $module . '/Views')) {
                mkdir(CUSTOMPATH . 'modules/' . $module . '/Views', 0775);
            }
        } else {
            // module exist
        }

        $module_info = include CUSTOMPATH . "modules/Admin/Config/ModuleBuilders/{$module}/vardefs.php";

        // create config
        if (!file_exists(CUSTOMPATH . "modules/{$module}/Config/{$module_info['model']}_vardef.php")) {
            $this->writeConfig("modules/{$module}/Config/{$module_info['model']}_vardef.php", $module_info, false);
        }

        // create controller
        $this->createController($module, $module, $module_info['model']);

        // create model
        if (!empty($module_info['model'])) {
            $this->createModel($module, $module_info['model'], $module_info['table']);
            $this->createAPIController($module, $module, $module_info['model']);
        }
    }

    public function cacheModules()
    {
        $modules = [];
        $databases = [];
        // scan core modules
        $module_dirs = scandir(APPPATH . 'modules');
        $custom_module_dirs = scandir(CUSTOMPATH . 'modules');
        $module_dirs = array_merge($module_dirs, $custom_module_dirs);
        foreach ($module_dirs as $dir) {
            if ($dir != '.'
                && $dir != '..'
                && (is_dir(APPPATH . 'modules/' . $dir) || is_dir(CUSTOMPATH . 'modules/' . $dir))) {
                $config_file = 'modules/' . $dir . '/Config/Config.php';
                $config = null;
                if (file_exists(CUSTOMPATH . $config_file)) {
                    $config = include CUSTOMPATH . $config_file;
                } else if (file_exists(APPPATH . $config_file)) {
                    $config = include APPPATH . $config_file;
                }
                if ($config && $config['module'] && $config['model']) {
                    $modules[$dir] = [
                        'module' => $config['module'],
                        'model' => $config['model'],
                    ];
                    if (!empty($config['database']) && !empty($config['database']['name'])) {
                        $database = $config['database'];
                        $databases[$database['name']] = $database;
                    }
                }
            }
        }
        // write to config
        write_array2file('modules/Admin/Config/modules.php', $modules, 2);
        write_array2file('modules/Admin/Config/databases.php', $databases, 2);

        return [
            'modules' => $modules,
            'databases' => $databases,
        ];
    }
}