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
    static $css = [];
    static $styles = [];
    static $js = [];
    static $scripts = [];

    /**
     * @var Node
     */
    public $source;
    protected $maxDepth = 5;
    protected $group = 0;
    protected $name = 'items';

    public static function source($source)
    {
        if (!$source instanceof Node) {
            throw new \InvalidArgumentException('DataTree only works with Baum\Node instances');
        }
        $instance = parent::source($source);
        $instance->attr('data-instance-id', spl_object_hash($instance));
        static::css('css/datatree.css');
        static::js('js/nestable/jquery.nestable.js');
        static::js('js/datatree.js');
        return $instance;
    }

    public function build($view = '')
    {
        $this->initJsWidget();

        $view == '' and $view = 'datatree::datatree';

        $this->open = \Form::open($this->attributes);
        $this->close = \Form::hidden('save', 1) . \Form::close();

        if (\Request::method() == 'POST') {
            $this->performSave();
        }

        $this->data = $this->source->getDescendants()->toHierarchy();
        Persistence::save();
        $this->rows = $this->makeRowsRecursive($this->data);

        return \View::make($view, array('dg' => $this, 'buttons' => $this->button_container, 'label' => $this->label));
    }

    protected function makeRowsRecursive($data, $depth = 0)
    {
        $rows = [];
        foreach ($data as $item) {
            $row = $this->makeRow($item);
            $row->children = $this->makeRowsRecursive($item['children'], $depth + 1);
            $rows[] = $row;
        }
        return $rows;
    }

    protected function performSave()
    {
        $var = \Input::get($this->name);
        if (is_string($var)) {
            $var = json_decode($var, true);
        }
        $this->saveItemsRecursive($this->source->getKey(), $var);
    }

    protected function saveItemsRecursive($parentId, $children)
    {
        try {

            $model = $this->source->getModel();
            foreach (array_reverse((array)$children) as $child) {
                $first = $model->find($parentId)->children()->first();
                $childModel = $model->find($child['id']);
                if (!$first || !$first->equals($childModel)) {
                    $childModel->makeFirstChildOf($model->find($parentId));
                }

                if (!empty($child['children'])) {
                    $this->saveItemsRecursive($child['id'], $child['children']);
                }
            }
        } catch (\Exception $e) {
//            var_dump($parentId, $child['id']);
//            xxx($model->find($parentId)->children()->first());
//            xxx($model->find($child['id']) , $model->find($parentId));
            throw $e;
        }
    }

    protected function makeRow($item)
    {
        $row = new Row($item);

        $row->children = array();

        $row->attributes(array(
            'class' => 'datatree-item',
            'data-id' => $row->data->getKey()
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
     * @param array $options
     *
     * @return $this
     */
    public function submit($name, $position = "BL", $options = array())
    {
        $options = array_merge(array("class" => "btn btn-primary"), $options);
        $this->button_container[$position][] = \Form::submit($name, $options);

        return $this;
    }

    public function maxDepth($value = null)
    {
        if (func_num_args()) {
            $this->maxDepth = $value;
            return $this;
        }
        return $this->maxDepth;
    }

    public function group($value = null)
    {
        if (func_num_args()) {
            $this->group = $value;
            return $this;
        }
        return $this->group;
    }

    public function name($value = null)
    {
        if (func_num_args()) {
            $this->name = $value;
            return $this;
        }
        return $this->name;
    }

    // inline script

    public function initJsWidget()
    {
        $script = '

$("[data-instance-id=\\"' . $this->attributes['data-instance-id'] . '\\"]").each(function(){
 var root = $(this);
 var form = root.find(".datatree-values");
 root.find(".datatree-inner-wrapper").nestable({
        listNodeName: "ol",
        itemNodeName: "li",
        rootClass: "datatree-inner-wrapper",
        listClass: "datatree-list",
        itemClass: "datatree-item",
        dragClass: "datatree-dragel",
        handleClass: "datatree-handle",
        collapsedClass: "datatree-collapsed",
        placeClass: "datatree-placeholder",
        noDragClass: "datatree-nodrag",
        emptyClass: "datatree-empty",
        expandBtnHTML: "<button data-action=\"expand\" type=\"button\">Expand</button>",
        collapseBtnHTML: "<button data-action=\"collapse\" type=\"button\">Collapse</button>",
        group: ' . $this->group . ',
        maxDepth: ' . $this->maxDepth . ',
        threshold: 20
    }).on("mousedown", "a", function (e) {
        e.stopImmediatePropagation();
    }).each(function () {
        var ol = $(this).children(".datatree-list");
        if (ol.length) rapyd.datatree.updateDepth(ol);
        rapyd.datatree.updateForm($(this), form, "' . $this->name . '");
    }).on("change", function () {
        var ol = $(this).children(".datatree-list");
        if (ol.length) rapyd.datatree.updateDepth(ol);
        var updated = rapyd.datatree.updateForm($(this), form, "' . $this->name . '");
        // $(this).parents(".datatree").first().submit();

    });
    $(".datatree").submit(function () {
        var action = $(this).attr("action") || document.location.href;
        //return false;
    });
 });
        ';

        static::$scripts[] = $script;
    }

    // copy paste from Rapyd

    public static function scripts()
    {
        $buffer = "\n";

        //js links
        foreach (self::$js as $item) {
            $buffer .= \HTML::script($item);
        }

        //inline scripts
        if (count(self::$scripts)) {
            $buffer .= sprintf("\n<script language=\"javascript\" type=\"text/javascript\">\n\$(document).ready(function () {\n\n %s \n\n});\n\n</script>\n", implode("\n", self::$scripts));
        }

        return $buffer;
    }

    public static function styles()
    {
        $buffer = "\n";

        //css links
        foreach (self::$css as $item) {
            $buffer .= \HTML::style($item);
        }

        //inline styles
        if (count(self::$styles)) {
            $buffer .= sprintf("<style type=\"text/css\">\n%s\n</style>", implode("\n", self::$styles));
        }

        return $buffer;
    }

    public static function js($js)
    {
        if (!in_array('packages/tacone/rapyd-datatree/assets/' . $js, self::$js))
            self::$js[] = 'packages/tacone/rapyd-datatree/assets/' . $js;
    }

    public static function css($css)
    {
        if (!in_array('packages/tacone/rapyd-datatree/assets/' . $css, self::$css))
            self::$css[] = 'packages/tacone/rapyd-datatree/assets/' . $css;
    }


}