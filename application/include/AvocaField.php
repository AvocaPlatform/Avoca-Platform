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


namespace Avoca;


class AvocaField
{
    public function format($value, $record, $option = '')
    {
        if (!$option) {
            return $value;
        }

        if (is_array($option) && !empty($option['type'])) {
            $type = $option['type'];
        } else if (is_string($option)) {
            $type = $option;
        } else {
            $type = 'text';
        }

        $type = ucfirst($type);
        $class = "\\Avoca\\Fields\\$type";
        $custom = "\\Custom\\Fields\\$type";
        $module = "\\App\\Modules\\Fields\\$type";
        $module_custom = "\\Custom\\Modules\\Fields\\$type";
        if (class_exists($module_custom)) {
            $class = $module_custom;
        } else if (class_exists($module)) {
            $class = $module;
        } else if (class_exists($custom)) {
            $class = $custom;
        }

        if (class_exists($class)
            && method_exists($class, 'format')) {
            $fieldModel = new $class();
            $fieldModel->format($value, $record, $option);
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

        // custom form
        $typeClass = ucfirst($type);
        $class = "\\Avoca\\Fields\\$typeClass";
        $custom = "\\Custom\\Fields\\$typeClass";
        $module = "\\App\\Modules\\Fields\\$typeClass";
        $module_custom = "\\Custom\\Modules\\Fields\\$typeClass";
        if (class_exists($module_custom)) {
            $class = $module_custom;
        } else if (class_exists($module)) {
            $class = $module;
        } else if (class_exists($custom)) {
            $class = $custom;
        }

        if (class_exists($class)
            && method_exists($class, 'form')) {
            $fieldModel = new $class();
            return $fieldModel->form($field, $value, $extra);
        }

        // default form
        switch ($type) {
            case 'number':
            case 'text':
                return form_input($field, $value, $extra);

            case 'select':
                $options = (!empty($option['options'])) ? $option['options'] : [];
                unset($extra['options']);
                return form_dropdown($field, $options, $value, $extra);

            case 'multiselect':
                $options = (!empty($option['options'])) ? $option['options'] : [];
                unset($extra['options']);
                return form_multiselect($field, $options, $value, $extra);

            case 'textarea':
                return form_textarea($field, $value, $extra);

            case 'password':
                return form_password($field, $value, $extra);

            default:
                return form_input($field, $value, $extra);
        }
    }
}