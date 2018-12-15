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


$field_helper_path_c = CUSTOMPATH . 'helpers/field_func.php';
if (file_exists($field_helper_path_c)) {
    include_once $field_helper_path_c;
} else {
    $field_helper_path = APPPATH . 'helpers/field_func.php';
    if (file_exists($field_helper_path)) {
        include_once $field_helper_path;
    }
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
        $function_name = 'fieldFormat_' . $type;
        if (function_exists($function_name)) {
            return $function_name($value, $record, $option);
        }

        $method_name = 'format_' . $type;
        if (method_exists($this, $method_name)) {
            return $this->$method_name($value, $record, $option);
        }

        return $value;
    }

    public function form($field, $value, $option = [])
    {
        if ($option) {
            if (!is_array($option)) {
                $type = 'text';
                $extra = [
                    'class' => 'form-control',
                ];
            } else {
                $type = (empty($option['type'])) ? 'text' : $option['type'];
                $type = strtolower($type);
                $extra = $option;
                unset($extra['type']);
                $extra['class'] = (!empty($option['class'])) ? $option['class'] : 'form-control';
            }
        } else {
            $type = 'disabled';
            $option = $extra = [
                'class' => 'form-control',
            ];
        }

        switch ($type) {
            case 'number':
            case 'text':
                return form_input($field, $value, $extra);

            case 'select':
                $options = (!empty($option['options'])) ? $option['options'] : [];
                return form_dropdown($field, $options, $value, $extra);

            case 'multiselect':
                $options = (!empty($option['options'])) ? $option['options'] : [];
                return form_multiselect($field, $options, $value, $extra);

            case 'textarea':
                return form_textarea($field, $value, $extra);

            case 'password':
                return form_password($field, $value, $extra);

            default:
                $function_name = 'fieldForm_' . $type;
                if (function_exists($function_name)) {
                    return $function_name($field, $value, $option);
                }

                $method_name = 'form_' . $type;
                if (method_exists($this, $method_name)) {
                    return $this->$method_name($value, $value, $option);
                }

                return form_input($field, $value, $extra);
        }
    }

    protected function format_link($value, $record, $option)
    {
        if (!empty($option['controller'])) {
            return '<a href="' . avoca_manage('/' . $option['controller'] . '/record/' . recordFVal($record, 'id')) . '">' . $value . '</a>';
        }

        return $value;
    }
}