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


class Setting extends AVC_Model
{
    protected $table = 'settings';

    /**
     * convert data to ci create table structure
     * @return array
     */
    public function readDatabaseStructure()
    {
        $databases = include APPPATH . 'modules/admin/config/databases.php';
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

    public function getModules($raw = false)
    {
        $modules = include APPPATH . 'modules/admin/config/modules.php';
        if ($raw) {
            return $modules;
        }

        $allModules = [];
        foreach ($modules as $module) {
            $is_created = true;
            if (!is_dir(APPPATH . 'modules/' . $module['module'])) {
                $is_created = false;
            }

            $allModules[] = [
                'module' => $module['module'],
                'model' => $module['model'],
                'is_created' => $is_created,
            ];
        }

        return $allModules;
    }
}