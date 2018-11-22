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


namespace Avoca\Libraries;


$field_helper_path_c = CUSTOMPATH . 'helpers/field_helper.php';
if (file_exists($field_helper_path_c)) {
    include_once $field_helper_path_c;
}

class AvocaField
{
    public function format($value, $record, $option = '')
    {
        if (!$option) {
            return $value;
        }

        if (is_array($option) && !empty($option['type'])) {
            $type = $option['type'];
        } else {
            $type = $option;
        }

        $type = strtolower($type);

        if (method_exists($this, $type)) {
            return $this->$type($value, $record, $option);
        }

        return $value;
    }

    protected function link($value, $record, $option)
    {
        if (function_exists('field_link')) {
            return field_link($value, $record, $option);
        }

        if (!empty($option['controller'])) {
            return '<a href="' . avoca_manage('/' . $option['controller'] . '/detail/' . recordFVal($record, 'id')) . '">' . $value . '</a>';
        }

        return $value;
    }
}