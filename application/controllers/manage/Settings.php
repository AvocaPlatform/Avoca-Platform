<?php
/**
 * Created by UP5 Tech & YouAddOn.
 * Website: https://up5.vn
 * User: Jacky
 * Email: hungtran@up5.vn | jacky@youaddon.com
 * Person: tdhungit@gmail.com
 * Skype: tdhungit
 * Git: https://github.com/tdhungit
 */

class Settings extends AVC_AdminController
{
    protected $model = 'Setting';

    private function readDatabaseStructure()
    {
        $databases = include APPPATH . 'avoca/databases.php';
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
    }

    public function create_module($module_id = null)
    {

    }
}