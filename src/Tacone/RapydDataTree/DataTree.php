<?php

namespace Tacone\RapydDataTree;

use Baum\Node;
use Zofe\Rapyd\DataGrid\Cell;
use Zofe\Rapyd\DataGrid\DataGrid;
use Zofe\Rapyd\DataGrid\Row;
use Zofe\Rapyd\DataSet;
use Zofe\Rapyd\Persistence;

class DataTree extends DataGrid
{
    public $attributes = array("class" => "datatree", "method" => "POST");
    public $data;
    /**
     * @var Node
     */
    public $source;

    public static function source($source)
    {
        if (!$source instanceof Node) {
            throw new \InvalidArgumentException('DataTree only works with Baum\Node instances');
        }
        return parent::source($source);
    }

    public function build($view = '')
    {
        $view == '' and $view = 'datatree::datatree';

        $this->open = \Form::open($this->attributes);
        $this->close = \Form::hidden('save', 1) . \Form::close();

        if (\Request::method() == 'POST') {
            $this->performSave();
        }

        $this->data = $this->source->getDescendants()->toHierarchy();
        Persistence::save();
        foreach ($this->data as $item) {
            $row = $this->makeRow($item);
            foreach ($item['children'] as $children) {
                $row->children[] = $this->makeRow($children);
            }

            $this->rows[] = $row;
        }
        return \View::make($view, array('dg' => $this, 'buttons' => $this->button_container, 'label' => $this->label));
    }
    protected function performSave() {
        
    }
    protected function makeRow($item)
    {
        $row = new Row($item);
        $row->children = array();

        $row->attributes(array(
            'class' => 'datatree-item',
        ));
        $index = 0;
        foreach ($this->columns as $column) {
            $index++;

            $cell = new Cell($column->name);
            $attrs = array();
            $attrs['data-field-name'] = strpos($column->name,
                '{{') === false ? $column->name : '_blade_' . $index;
            $cell->attributes($attrs);

            $sanitize = (count($column->filters) || $column->cell_callable) ? false : true;
            $value = $this->getCellValue($column, $item, $sanitize);
            $cell->value($value);
            $cell->parseFilters($column->filters);
            if ($column->cell_callable) {
                $callable = $column->cell_callable;
                $cell->value($callable($cell->value));
            }

            $row->add($cell);
        }

        if (count($this->row_callable)) {
            foreach ($this->row_callable as $callable) {
                $callable($row);
            }
        }
        return $row;
    }

    /**
     * @param string $name
     * @param string $position
     * @param array  $options
     *
     * @return $this
     */
    public function submit($name, $position = "BL", $options = array())
    {
        $options = array_merge(array("class" => "btn btn-primary"), $options);
        $this->button_container[$position][] = \Form::submit($name, $options);

        return $this;
    }
}