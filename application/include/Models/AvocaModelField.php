<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 1/30/19
 * Time: 3:38 PM
 */

namespace Avoca\Models;


use Avoca\AvocaField;

class AvocaModelField
{
    /**
     * @var AvocaModel
     */
    protected $model;

    protected $vardefs;
    protected $viewdefs;

    public function __construct($model)
    {
        $this->model = $model;
        $this->vardefs = $this->model->getFieldDefs();
        $this->viewdefs = $this->model->getLayoutDefs();
    }

    public function label($name, $option)
    {
        if (!empty($option['label'])) {
            return $option['label'];
        }

        if (isset($this->vardefs['fields'][$name]['label'])) {
            return $this->vardefs['fields'][$name]['label'];
        }

        return ucfirst($name);
    }

    public function value($name, $record, $option = [])
    {
        if (!isset($option['type'])) {
            $option['type'] = $this->vardefs['fields'][$name]['type'];
        }

        return AvocaField::format($name, $record, $option);
    }

    public function replace($str, $record)
    {
        $id = isset($record['id']) ? $record['id'] : '';
        return str_replace('{ID}', $id, $str);
    }
}