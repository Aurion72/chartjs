<?php

namespace Aurion72\ChartJs;

class ChartJs
{
    private $width;

    private $height;

    private $canvasStyles = [];

    private $canvasClasses = '';

    private $type;

    private $name;

    private $show_axes;

    private $options;

    private $legend;

    private $scales = [
        'xAxes' => [],
        'yAxes' => [],
    ];

    private $labels = [];

    private $animation;

    private $elements;

    private $events;

    private $layout;

    private $hover;

    private $plugins;

    private $title;

    private $tooltips;

    private $customs = [];

    private $static_function_attributes = [
        'options.animation.onComplete'            => 'animation',
        'options.animation.onProgress'            => 'animation',
        'options.hover.onHover'                   => 'chart',
        'options.legendCallback'                  => 'chart',
        'options.legend.onClick'                  => 'chart',
        'options.legend.labels.generateLabels'    => 'chart',
        'options.legend.onHover'                  => 'chart',
        'options.tooltips.callbacks.afterBody'    => 'tooltipItems, data',
        'options.tooltips.callbacks.afterFooter'  => 'tooltipItems, data',
        'options.tooltips.callbacks.afterLabel'   => 'tooltipItems, data',
        'options.tooltips.callbacks.afterTitle'   => 'tooltipItems, data',
        'options.tooltips.callbacks.beforeBody'   => 'tooltipItems, data',
        'options.tooltips.callbacks.beforeFooter' => 'tooltipItems, data',
        'options.tooltips.callbacks.beforeLabel'  => 'tooltipItems, data',
        'options.tooltips.callbacks.beforeTitle'  => 'tooltipItems, data',
        'options.tooltips.callbacks.footer'       => 'tooltipItems, data',
        'options.tooltips.callbacks.label'        => 'tooltipItems, data',
        'options.tooltips.callbacks.labelColor'   => 'tooltipItems, data',
        'options.tooltips.callbacks.title'        => 'tooltipItems, data',
    ];

    private $dynamic_function_attributes = [
        'options.scales.xAxes.[*].ticks.callback' => 'value, index, values',
        'options.scales.yAxes.[*].ticks.callback' => 'value, index, values',
    ];

    private $xLabels = null;

    private $yLabels = null;

    private $datasets = [];

    private $current_axis = 'xAxes';

    private $current_dataset_id;

    private $axes_ids = ['xAxes' => [], 'yAxes' => []];

    private $axes_without_id = [];

    private $current_axis_id = 0;

    private $use_same_color_background_and_border = true;

    private $debug_mode = false;

    private $experimental_legend = false;

    private $canvas_id = null;

    private $default_charts_colors;

    private $config;

    private static $timeout_duration = 10;

    private $separate_canvas_and_js;

    public function __construct($type = null, $width = null, $height = null, $name = null)
    {
        $this->config = config('aurion_chartjs');
        $this->width = $width ? $width : config('aurion_chartjs.options.width');
        $this->height = $height ? $height : config('aurion_chartjs.options.height');
        $this->type = $type ? $type : config('aurion_chartjs.options.type');
        $this->name = $name;
        $this->show_axes = config('aurion_chartjs.options.showAxes');
        $this->options = config('aurion_chartjs.options.general', []);
        $this->scales = config('aurion_chartjs.options.scales', []);
        $this->legend = config('aurion_chartjs.options.legend', []);
        $this->layout = config('aurion_chartjs.options.layout', []);
        $this->animation = config('aurion_chartjs.options.animation', []);
        $this->tooltips = config('aurion_chartjs.options.tooltips', []);
        $this->elements = config('aurion_chartjs.options.elements', []);
        $this->events = config('aurion_chartjs.options.events', []);
        $this->hover = config('aurion_chartjs.options.hover', []);
        $this->plugins = config('aurion_chartjs.options.plugins', []);
        $this->title = config('aurion_chartjs.options.title');

        $this->default_charts_colors = config('aurion_chartjs.default.default_charts_colors');
        $this->use_same_color_background_and_border = config('aurion_chartjs.default.use_same_color_background_and_border');
        $this->separate_canvas_and_js = config('aurion_chartjs.default.separate_canvas_and_js');

        $this->initScales();
    }

    public function useSameColorForBackgroundAndBorder(bool $value)
    {
        $this->use_same_color_background_and_border = $value;

        return $this;
    }

    public function separateCanvasAndJs(bool $value)
    {
        $this->separate_canvas_and_js = $value;

        return $this;
    }

    public function defaultCharsColors(array $value)
    {
        $this->default_charts_colors = $value;

        return $this;
    }

    public function canvasId($id)
    {
        $this->canvas_id = $id;

        return $this;
    }

    public function debugMode(bool $value)
    {
        $this->debug_mode = $value;

        return $this;
    }

    public function showAxes(bool $value)
    {
        $this->show_axes = $value;

        return $this;
    }

    public function experimentalLegend(bool $value)
    {
        $this->experimental_legend = $value;

        return $this;
    }

    public function setCanvasClasses(string $classes)
    {
        $this->canvasClasses = $classes;
    }

    public function setCanvasStyles(array $styles)
    {
        $this->canvasStyles = $styles;
    }

    /*
     * General
     * */

    public function getOptionsJson($escape_quotes = false)
    {
        return json_encode($this->getOptions(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getScalesJson($escape_quotes = false)
    {
        return json_encode($this->getScales(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    private function initScales()
    {

        foreach ($this->scales as $axis => $ids) {
            if (!is_array($ids)) {
                continue;
            }
            foreach ($ids as $id => $values) {

                if (isset($value['id'])) {
                    $this->scales[$axis][$values['id']] = $values;
                } else {
                    $uniqid = uniqid();
                    $values['id'] = $uniqid;
                    $this->axes_without_id[] = $uniqid;
                    $this->scales[$axis][$uniqid] = $values;
                }

                $this->axes_ids[$axis][] = $values['id'];
                unset($this->scales[$axis][$id]);
            }
        }

        $this->setCurrentAxisId();
    }

    public function getScales()
    {
        $raw_scales = $this->scales;

        foreach ($raw_scales as $key => $axis) {
            if (!is_array($axis)) {
                continue;
            }
            foreach ($axis as $k => $axis_id_array) {
                if (in_array($axis_id_array['id'], $this->axes_without_id)) {
                    unset($axis_id_array['id']);
                }
                unset($axis[$k]);
                $axis[] = $axis_id_array;
            }
            $raw_scales[$key] = array_values($axis);
        }

        return $raw_scales;
    }

    public function getAnimationJson($escape_quotes = false)
    {
        return json_encode($this->getAnimation(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    public function getAnimation()
    {
        return $this->animation;
    }

    public function getLegendJson($escape_quotes = false)
    {
        return json_encode($this->getLegend(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    public function getLegend()
    {
        return $this->legend;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function getHover()
    {
        return $this->hover;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function getPlugins()
    {
        return $this->plugins;
    }

    public function getTitle()
    {
        return $this->title;
    }

    private function convertFunctions(array $render_array)
    {
        $function_attributes = $this->generateFunctionAttributes($render_array);

        $array_dot = array_dot($render_array);

        $function = "function (function_parameters) { return window['function_name'](function_parameters); }";

        foreach ($function_attributes as $name => $parameters) {

            if (array_key_exists($name, $array_dot)) {
                if (!$array_dot[$name]) {
                    unset($array_dot[$name]);
                    continue;
                } else {
                    $array_dot[$name] = str_replace('function_name', $array_dot[$name], $function);
                    $array_dot[$name] = str_replace('function_parameters', $parameters, $array_dot[$name]);
                }
            }
        }

        $converted_render_array = [];
        foreach ($array_dot as $key => $value) {
            array_set($converted_render_array, $key, $value);
        }

        return $converted_render_array;
    }

    private function generateFunctionAttributes(array $render_array)
    {
        $static_function_attributes = $this->static_function_attributes;
        $dynamic_function_attributes = $this->dynamic_function_attributes;

        $new_dynamatic_pathes = [];

        foreach ($dynamic_function_attributes as $path => $attribute) {
            $path_split = explode('.[*].', $path);
            if (array_key_exists('0', $path_split) && array_key_exists('1', $path_split)) {
                $first_part = array_get($render_array, $path_split[0], array());
                foreach ($first_part as $id => $part) {
                    $new_dynamatic_pathes[$path_split[0].'.'.$id.'.'.$path_split[1]] = $attribute;
                }
            }
        }

        $function_attributes = array_merge($static_function_attributes, $new_dynamatic_pathes);

        return $function_attributes;
    }

    public function getTooltipsJson($escape_quotes = false)
    {
        return json_encode($this->getTooltips(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    public function getTooltips()
    {
        return $this->tooltips;
    }

    public function getLabelsJson($escape_quotes = false)
    {
        return json_encode($this->getLabels(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    public function getLabels()
    {
        $labels = $this->labels;
        if (count($labels) == 0) {
            $labels = [''];
        }

        return $labels;
    }

    public function setXLabels(array $labels)
    {
        return $this->xLabels = $labels;
    }

    public function setYLabels(array $labels)
    {
        return $this->yLabels = $labels;
    }

    public function getXLabels()
    {
        return $this->xLabels;
    }

    public function getYLabels()
    {
        return $this->yLabels;
    }

    public function getDatasetsJson($escape_quotes = false)
    {
        return json_encode($this->getDatasets(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    public function getCustoms()
    {
        return $this->customs;
    }

    public function getDatasets()
    {
        $raw_datasets = $this->datasets;
        foreach ($raw_datasets as $key => $dataset) {
            if ($this->use_same_color_background_and_border) {
                if (isset($dataset['backgroundColor']) && !isset($dataset['borderColor'])) {
                    $dataset['borderColor'] = $dataset['backgroundColor'];
                } elseif (isset($dataset['borderColor']) && !isset($dataset['backgroundColor'])) {
                    $dataset['backgroundColor'] = $dataset['borderColor'];
                } elseif (isset($dataset['backgroundColor']) && isset($dataset['borderColor'])) {
                    $dataset['borderColor'] = $dataset['backgroundColor'];
                } else {
                    $dataset['backgroundColor'] = $this->default_charts_colors[count($this->default_charts_colors) - 1];
                }
            }
            unset($raw_datasets[$key]);
            $raw_datasets[] = $dataset;
        }

        return $raw_datasets;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(string $value)
    {
        $this->type = $value;

        return $this;
    }

    public function setDimensions(int $width = null, int $height = null)
    {
        if ($width) {
            $this->width = $width;
        }
        if ($height) {
            $this->height = $height;
        }

        return $this;
    }

    public function setResponsive(bool $value)
    {
        $this->options['responsive'] = $value;

        return $this;
    }

    public function setMaintainAspectRatio(bool $value)
    {
        $this->options['maintainAspectRatio'] = $value;

        return $this;
    }

    public function setDecimal(int $value)
    {
        $this->options['decimal'] = $value;

        return $this;
    }

    /*
     * Animation
     * */

    public function setAnimationDuration(float $value)
    {
        return $this->setting('animation', 'duration', $value);
    }

    public function setAnimationEasing(string $value)
    {
        return $this->setting('animation', 'easing', $value);
    }

    public function setAnimationOnCompleteCallback(string $value)
    {
        return $this->setting('animation', 'onComplete', $value);
    }

    public function setAnimationOnProgressCallback(string $value)
    {
        return $this->setting('animation', 'onProgress', $value);
    }

    /*
         * Axes
         * */

    public function setCurrentAxis($axis, $id = null)
    {
        if (!strstr($axis, 'Axes')) {
            $axis = $axis.'Axes';
        }
        $this->current_axis = $axis;

        if (count($this->axes_ids[$this->current_axis]) == 0) {
            $this->addAxisId(null);
        } else {

            $this->setCurrentAxisId($id);
        }

        return $this;
    }

    public function addAxisId($id, bool $set_current = true)
    {
        if (is_null($id)) {
            $id = uniqid();
            $this->axes_without_id[] = $id;
        }

        if (in_array($id, $this->axes_ids[$this->current_axis])) {
            return $this;
        }
        $this->axes_ids[$this->current_axis][] = $id;
        $this->scales[$this->current_axis][$id] = ['id' => $id];
        if ($set_current) {
            $this->setCurrentAxisId($id);
        }

        return $this;
    }

    public function setCurrentAxisId($id = null)
    {
        $selected_id = null;
        if (!is_null($id) && in_array($id, $this->axes_ids[$this->current_axis])) {
            $selected_id = $id;
        } elseif (count($this->axes_ids[$this->current_axis]) > 0) {
            $selected_id = array_first($this->axes_ids[$this->current_axis]);
        }

        $this->current_axis_id = $selected_id;

        return $this;
    }

    private function setAxisSetting($attribute, $value)
    {
        if (is_null($this->current_axis_id)) {
            return $this;
        }

        return $this->setting('scales', $this->axisAttributePath($attribute), $value);
    }

    private function modifyExistingAxisId($id)
    {
        $old_id = $this->current_axis_id;

        /* The old id no longer exists and now has its own id */
        if (in_array($old_id, $this->axes_without_id)) {
            unset($this->axes_without_id[array_search($old_id, $this->axes_without_id)]);
        }

        /* The new value replace the old */
        if (in_array($old_id, $this->axes_ids[$this->current_axis])) {
            unset($this->axes_ids[$this->current_axis][array_search($old_id, $this->axes_without_id)]);
            $this->axes_ids[$this->current_axis][] = $id;
        }

        /* Change the key of current axis id array */
        $current_axis_id_array = $this->scales[$this->current_axis][$this->current_axis_id];
        unset($this->scales[$this->current_axis][$this->current_axis_id]);
        $this->scales[$this->current_axis][$id] = $current_axis_id_array;

        $this->setCurrentAxisId($id);

        return $this->setAxisSetting('id', $id);
    }

    public function setAxisStacked(bool $value)
    {
        return $this->setAxisSetting('stacked', $value);
    }

    public function setAxisId($value)
    {
        return $this->modifyExistingAxisId($value);
    }

    public function setAxisDisplay(bool $value)
    {
        return $this->setAxisSetting('display', $value);
    }

    public function setAxisType(string $value)
    {
        return $this->setAxisSetting('type', $value);
    }

    public function setAxisCategoryPercentage(float $value)
    {
        return $this->setAxisSetting('categoryPercentage', $value);
    }

    public function setAxisPosition(string $value)
    {
        return $this->setAxisSetting('position', $value);
    }

    private function axisAttributePath($attribute)
    {
        return $this->current_axis.'.'.$this->current_axis_id.'.'.$attribute;
    }

    /*
     * Axis Ticks
     * */

    public function setAxisTicksFontSize(int $value)
    {
        return $this->setAxisSetting('ticks.fontSize', $value);
    }

    public function setAxisTicksMin(int $value)
    {
        return $this->setAxisSetting('ticks.min', $value);
    }

    public function setAxisTicksBeginAtZero(bool $value)
    {
        return $this->setAxisSetting('ticks.beginAtZero', $value);
    }

    public function setAxisTicksSuggestedMin(float $value)
    {
        return $this->setAxisSetting('ticks.suggestedMin', $value);
    }

    public function setAxisTicksSuggestedMax(float $value)
    {
        return $this->setAxisSetting('ticks.suggestedMax', $value);
    }

    public function setAxisTicksMinRotation(float $value)
    {
        return $this->setAxisSetting('ticks.minRotation', $value);
    }

    public function setAxisTicksAutoSkipPadding(float $value)
    {
        return $this->setAxisSetting('ticks.autoSkipPadding', $value);
    }

    public function setAxisTicksPadding(float $value)
    {
        return $this->setAxisSetting('ticks.padding', $value);
    }

    public function setAxisTicksMax(int $value)
    {
        return $this->setAxisSetting('ticks.max', $value);
    }

    public function setAxisTicksStepSize(float $value)
    {
        return $this->setAxisSetting('ticks.stepSize', $value);
    }

    public function setAxisTicksFontColor(string $value)
    {
        return $this->setAxisSetting('ticks.fontColor', $value);
    }

    public function setAxisTicksAutoSkip(bool $value)
    {
        return $this->setAxisSetting('ticks.autoSkip', $value);
    }

    public function setAxisTicksCallback(string $value)
    {
        return $this->setAxisSetting('ticks.callback', $value);
    }

    /*
     * Axis GridLine
     * */

    public function setAxisGridLinesColor(string $value)
    {
        return $this->setAxisSetting('gridLines.color', $value);
    }

    public function setAxisGridLinesTickMarkLength(float $value)
    {
        return $this->setAxisSetting('gridLines.tickMarkLength', $value);
    }

    public function setAxisGridLinesZeroLineColor(string $value)
    {
        return $this->setAxisSetting('gridLines.zeroLineColor', $value);
    }

    /*
     * Axis GridLine
     * */

    public function setAxisScaleLabelDisplay(bool $value)
    {
        return $this->setAxisSetting('scaleLabel.display', $value);
    }

    public function setAxisScaleLabelLabelString(string $value)
    {
        return $this->setAxisSetting('scaleLabel.labelString', $value);
    }

    public function setAxisScaleLabelLineHeight(float $value)
    {
        return $this->setAxisSetting('scaleLabel.lineHeight', $value);
    }

    /*
    * Datasets
    * */

    private function datasetAttributePath($attribute, $id = null)
    {
        if (is_null($id)) {
            $id = $this->current_dataset_id;
        }

        return $id.'.'.$attribute;
    }

    private function setDataSetting($attribute, $value, bool $all_datasets = false)
    {
        if (!$all_datasets) {
            $this->setting('datasets', $this->datasetAttributePath($attribute), $value);
        } else {
            foreach ($this->datasets as $key => $i) {
                $this->setting('datasets', $this->datasetAttributePath($attribute, $key), $value);
            }
        }

        return $this;
    }

    public function resetDatasets()
    {
        $this->datasets = [];

        return $this;
    }

    public function addData($data, $id = null, $use_id_as_label = true)
    {
        if (is_null($id)) {
            $id = uniqid();
            if ($use_id_as_label === true) {
                $use_id_as_label = false;
            }
        }
        if (!is_object($data) && !is_array($data)) {
            $data = [$data];
        }
        $this->datasets[$id] = ['data' => $data];
        if ($use_id_as_label) {
            $this->datasets[$id]['label'] = is_string($use_id_as_label) ? $use_id_as_label : $id;
        }

        $this->setCurrentDatasetId($id);

        return $this;
    }

    public function setCurrentDatasetId($id)
    {
        $this->current_dataset_id = $id;

        return $this;
    }

    public function setDataType(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('type', $value, $all_datasets);
    }

    public function setDataLabel(string $value, bool $all_datasets = false)
    {
        $current_datasets = $this->datasets[$this->current_dataset_id];
        unset($this->datasets[$this->current_dataset_id]);
        $this->datasets[$value] = $current_datasets;
        $this->setCurrentDatasetId($value);

        return $this->setDataSetting('label', $value, $all_datasets);
    }

    public function setData(array $value)
    {
        return $this->setDataSetting('data', $value);
    }

    public function setDataYAxisId($value, bool $all_datasets = false)
    {
        return $this->setDataSetting('yAxisID', $value, $all_datasets);
    }

    public function setDataXAxisId($value, bool $all_datasets = false)
    {
        return $this->setDataSetting('xAxisID', $value, $all_datasets);
    }

    public function setDataHoverBackgroundColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('hoverBackgroundColor', $value, $all_datasets);
    }

    public function setDataHoverBorderColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('hoverBorderColor', $value, $all_datasets);
    }

    public function setDataHoverBorderWidth(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('hoverBorderWidth', $value, $all_datasets);
    }

    public function setDataBorderWidth(int $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('borderWidth', $value, $all_datasets);
    }

    public function setDataBorderSkipped(int $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('borderSkipped', $value, $all_datasets);
    }

    public function setDataPointRadius(int $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointRadius', $value, $all_datasets);
    }

    public function setDataPointBorderColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointBorderColor', $value, $all_datasets);
    }

    public function setDataPointBackgroundColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointBackgroundColor', $value, $all_datasets);
    }

    public function setDataPointStyle(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointStyle', $value, $all_datasets);
    }

    public function setDataPointHoverRadius(int $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointHoverRadius', $value, $all_datasets);
    }

    public function setDataShowLine(int $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('showLine', $value, $all_datasets);
    }

    public function setDataLineTension(int $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('lineTension', $value, $all_datasets);
    }

    public function setDataFill(bool $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('fill', $value, $all_datasets);
    }

    public function setDataSpanGaps(bool $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('spanGaps', $value, $all_datasets);
    }

    public function setDataBorderColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('borderColor', $value, $all_datasets);
    }

    public function setDataBackgroundColor($value = null, bool $all_datasets = false)
    {
        if (!$value) {
            $value = $this->default_charts_colors;
        }

        return $this->setDataSetting('backgroundColor', $value, $all_datasets);
    }

    public function setDataColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('color', $value, $all_datasets);
    }

    public function setDataPointColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointColor', $value, $all_datasets);
    }

    public function setDataPointStrokeColor(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointStrokeColor', $value, $all_datasets);
    }

    public function setDataPointHighlightFill(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointHighlightFill', $value, $all_datasets);
    }

    public function setDataPointHighlightStroke(string $value, bool $all_datasets = false)
    {
        return $this->setDataSetting('pointHighlightStroke', $value, $all_datasets);
    }

    /*
     * Elements
     * */

    public function setElementsArcBackgroundColor(string $value)
    {
        return $this->setting('elements', 'arc.backgroundColor', $value);
    }

    public function setElementsArcBorderColor(string $value)
    {
        return $this->setting('elements', 'arc.borderColor', $value);
    }

    public function setElementsArcBorderWidth(float $value)
    {
        return $this->setting('elements', 'arc.borderWidth', $value);
    }

    public function setElementsLineBackgroundColor(string $value)
    {
        return $this->setting('elements', 'line.backgroundColor', $value);
    }

    public function setElementsLineBorderCapStyle(string $value)
    {
        return $this->setting('elements', 'line.borderCapStyle', $value);
    }

    public function setElementsLineBorderColor(string $value)
    {
        return $this->setting('elements', 'line.borderColor', $value);
    }

    public function setElementsLineBorderDash(array $value)
    {
        return $this->setting('elements', 'line.borderDash', $value);
    }

    public function setElementsLineBorderDashOffset(float $value)
    {
        return $this->setting('elements', 'line.borderDashOffset', $value);
    }

    public function setElementsLineBorderJoinStyle(string $value)
    {
        return $this->setting('elements', 'line.borderJoinStyle', $value);
    }

    public function setElementsLineBorderWidth(float $value)
    {
        return $this->setting('elements', 'line.borderWidth', $value);
    }

    public function setElementsLineCapBezierPoints(bool $value)
    {
        return $this->setting('elements', 'line.capBezierPoints', $value);
    }

    public function setElementsLineFill(bool $value)
    {
        return $this->setting('elements', 'line.fill', $value);
    }

    public function setElementsLineTension(float $value)
    {
        return $this->setting('elements', 'line.tension', $value);
    }

    public function setElementsPointBackgroundColor(string $value)
    {
        return $this->setting('elements', 'point.backgroundColor', $value);
    }

    public function setElementsPointBorderColor(string $value)
    {
        return $this->setting('elements', 'point.borderColor', $value);
    }

    public function setElementsPointBorderWidth(float $value)
    {
        return $this->setting('elements', 'point.borderWidth', $value);
    }

    public function setElementsPointHitRadius(float $value)
    {
        return $this->setting('elements', 'point.hitRadius', $value);
    }

    public function setElementsPointHoverBorderWidth(float $value)
    {
        return $this->setting('elements', 'point.hoverBorderWidth', $value);
    }

    public function setElementsPointHoverRadius(float $value)
    {
        return $this->setting('elements', 'point.hoverRadius', $value);
    }

    public function setElementsPointPointStyle($value)
    {
        return $this->setting('elements', 'point.pointStyle', $value);
    }

    public function setElementsPointRadius(float $value)
    {
        return $this->setting('elements', 'point.radius', $value);
    }

    public function setElementsRectangleBackgroundColor(string $value)
    {
        return $this->setting('elements', 'rectangle.backgroundColor', $value);
    }

    public function setElementsRectangleBorderColor(string $value)
    {
        return $this->setting('elements', 'rectangle.borderColor', $value);
    }

    public function setElementsRectangleBorderSkipped(float $value)
    {
        return $this->setting('elements', 'rectangle.borderSkipped', $value);
    }

    public function setElementsRectangleBorderWidth(float $value)
    {
        return $this->setting('elements', 'rectangle.borderWidth', $value);
    }

    /*
     * Events
     * */

    public function addEvent($event)
    {
        if (!in_array($event, $this->getEvents())) {
            $this->events[] = $event;
        }

        return $this;
    }

    public function removeEvent($event)
    {
        if ($this->getEvents($event) !== false) {
            unset($this->events[$event]);
        }

        return $this;
    }

    /*
     * Hover
     * */

    public function setHoverAnimationDuration(int $value)
    {
        return $this->setting('hover', 'animationDuration', $value);
    }

    public function setHoverIntersect(bool $value)
    {
        return $this->setting('hover', 'intersect', $value);
    }

    public function setHoverMode(string $value)
    {
        return $this->setting('hover', 'mode', $value);
    }

    public function setHoverOnHover(string $value)
    {
        return $this->setting('hover', 'onHover', $value);
    }

    /*
    * Labels
    * */

    public function addLabels($values)
    {
        if (is_array($values)) {
            $this->labels += $values;
        } else {
            $this->labels[] = $values;
        }

        return $this;
    }

    public function replaceLabels(array $new_labels)
    {
        $this->labels = $new_labels;

        return $this;
    }

    /*
     * Legend
     * */

    public function setLayoutPadding(float $value)
    {
        return $this->setting('layout', 'padding', $value);
    }

    public function setLayoutPaddingLeft(float $value)
    {
        return $this->setting('layout', 'padding.left', $value);
    }

    public function setLayoutPaddingTop(float $value)
    {
        return $this->setting('layout', 'padding.top', $value);
    }

    public function setLayoutPaddingRight(float $value)
    {
        return $this->setting('layout', 'padding.right', $value);
    }

    public function setLayoutPaddingBottom(float $value)
    {
        return $this->setting('layout', 'padding.bottom', $value);
    }

    /*
     * Legend
     * */

    public function setLegendDisplay(bool $value)
    {
        return $this->setting('legend', 'display', $value);
    }

    public function setLegendFullWidth(bool $value)
    {
        return $this->setting('legend', 'fullWidth', $value);
    }

    public function setLegendLabelsBoxWidth(float $value)
    {
        return $this->setting('legend', 'labels.boxWidth', $value);
    }

    public function setLegendLabelsGenerateLabels(string $value)
    {
        return $this->setting('legend', 'labels.generateLabels', $value);
    }

    public function setLegendLabelsPadding(float $value)
    {
        return $this->setting('legend', 'labels.padding', $value);
    }

    public function setLegendPosition(string $value)
    {
        return $this->setting('legend', 'position', $value);
    }

    public function setLegendOnClick(string $value)
    {
        return $this->setting('legend', 'onClick', $value);
    }

    public function setLegendOnHover(string $value)
    {
        return $this->setting('legend', 'onHover', $value);
    }

    public function setLegendReverse(bool $value)
    {
        return $this->setting('legend', 'reverse', $value);
    }

    public function setLegendWeight(float $value)
    {
        return $this->setting('legend', 'weight', $value);
    }

    /*
     * Plugins
     * */

    public function setPluginsSettings($key, $value)
    {
        return $this->setting('plugins', $key, $value);
    }

    /*
     * Title
     * */

    public function setTitleDisplay(bool $value)
    {
        return $this->setting('title', 'display', $value);
    }

    public function setTitleFontStyle(string $value)
    {
        return $this->setting('title', 'fontStyle', $value);
    }

    public function setTitleFullWidth(bool $value)
    {
        return $this->setting('title', 'fullWidth', $value);
    }

    public function setTitlePadding(float $value)
    {
        return $this->setting('title', 'padding', $value);
    }

    public function setTitlePosition(string $value)
    {
        return $this->setting('title', 'position', $value);
    }

    public function setTitleText(bool $value)
    {
        return $this->setting('title', 'text', $value);
    }

    public function setTitleWeight(bool $value)
    {
        return $this->setting('title', 'weight', $value);
    }

    /*
     * Tooltips
     * */

    public function setting($section, $attribute, $value)
    {
        $attribute_e = explode('.', $attribute);
        $arr = $value;
        foreach (array_reverse($attribute_e) as $attr) {
            $arr = [$attr => $arr];
        }

        $this->$section = array_replace_recursive($this->$section, $arr);

        return $this;
    }

    public function setTooltipsbackgroundColor(string $value)
    {
        return $this->setting('tooltips', 'backgroundColor', $value);
    }

    public function setTooltipsbodyAlign(string $value)
    {
        return $this->setting('tooltips', 'bodyAlign', $value);
    }

    public function setTooltipsbodyFontColor(string $value)
    {
        return $this->setting('tooltips', 'bodyFontColor', $value);
    }

    public function setTooltipsBodyFontSize(string $value)
    {
        return $this->setting('tooltips', 'bodyFontSize', $value);
    }

    public function setTooltipsBodySpacing(string $value)
    {
        return $this->setting('tooltips', 'bodySpacing', $value);
    }

    public function setTooltipsBorderColor(string $value)
    {
        return $this->setting('tooltips', 'borderColor', $value);
    }

    public function setTooltipsBorderWidth(string $value)
    {
        return $this->setting('tooltips', 'borderWidth', $value);
    }

    public function setTooltipsCallbacksAfterBody(string $value)
    {
        return $this->setting('tooltips', 'callbacks.afterBody', $value);
    }

    public function setTooltipsCallbacksAfterFooter(string $value)
    {
        return $this->setting('tooltips', 'callbacks.afterFooter', $value);
    }

    public function setTooltipsCallbacksAfterLabel(string $value)
    {
        return $this->setting('tooltips', 'callbacks.afterLabel', $value);
    }

    public function setTooltipsCallbacksAfterTitle(string $value)
    {
        return $this->setting('tooltips', 'callbacks.afterTitle', $value);
    }

    public function setTooltipsCallbacksBeforeBody(string $value)
    {
        return $this->setting('tooltips', 'callbacks.beforeBody', $value);
    }

    public function setTooltipsCallbacksBeforeFooter(string $value)
    {
        return $this->setting('tooltips', 'callbacks.beforeFooter', $value);
    }

    public function setTooltipsCallbacksBeforeLabel(string $value)
    {
        return $this->setting('tooltips', 'callbacks.beforeLabel', $value);
    }

    public function setTooltipsCallbacksBeforeTitle(string $value)
    {
        return $this->setting('tooltips', 'callbacks.beforeTitle', $value);
    }

    public function setTooltipsCallbacksFooter(string $value)
    {
        return $this->setting('tooltips', 'callbacks.footer', $value);
    }

    public function setTooltipsCallbacksLabel(string $value)
    {
        return $this->setting('tooltips', 'callbacks.label', $value);
    }

    public function setTooltipsCallbacksLabelColor(string $value)
    {
        return $this->setting('tooltips', 'callbacks.labelColor', $value);
    }

    public function setTooltipsCallbacksTitle(string $value)
    {
        return $this->setting('tooltips', 'callbacks.title', $value);
    }

    public function setTooltipsEventsMousemove(string $value)
    {
        return $this->setting('tooltips', 'events.mousemove', $value);
    }

    public function setTooltipsEventsTouchstart(string $value)
    {
        return $this->setting('tooltips', 'events.touchstart', $value);
    }

    public function setTooltipsEventsTouchmove(string $value)
    {
        return $this->setting('tooltips', 'events.touchmove', $value);
    }

    public function setTooltipsCaretPadding(string $value)
    {
        return $this->setting('tooltips', 'caretPadding', $value);
    }

    public function setTooltipsCaretSize(string $value)
    {
        return $this->setting('tooltips', 'caretSize', $value);
    }

    public function setTooltipsCornerRadius(string $value)
    {
        return $this->setting('tooltips', 'cornerRadius', $value);
    }

    public function setTooltipsCustom(string $value)
    {
        return $this->setting('tooltips', 'custom', $value);
    }

    public function setTooltipsDisplayColors(string $value)
    {
        return $this->setting('tooltips', 'displayColors', $value);
    }

    public function setTooltipsEnabled(string $value)
    {
        return $this->setting('tooltips', 'enabled', $value);
    }

    public function setTooltipsFooterAlign(string $value)
    {
        return $this->setting('tooltips', 'footerAlign', $value);
    }

    public function setTooltipsFooterFontColor(string $value)
    {
        return $this->setting('tooltips', 'footerFontColor', $value);
    }

    public function setTooltipsFooterFontStyle(string $value)
    {
        return $this->setting('tooltips', 'footerFontStyle', $value);
    }

    public function setTooltipsFooterMarginTop(string $value)
    {
        return $this->setting('tooltips', 'footerMarginTop', $value);
    }

    public function setTooltipsFooterSpacing(string $value)
    {
        return $this->setting('tooltips', 'footerSpacing', $value);
    }

    public function setTooltipsIntersect(string $value)
    {
        return $this->setting('tooltips', 'intersect', $value);
    }

    public function setTooltipsMode(string $value)
    {
        return $this->setting('tooltips', 'mode', $value);
    }

    public function setTooltipsMultiKeyBackground(string $value)
    {
        return $this->setting('tooltips', 'multiKeyBackground', $value);
    }

    public function setTooltipsPosition(string $value)
    {
        return $this->setting('tooltips', 'position', $value);
    }

    public function setTooltipsTitleAlign(string $value)
    {
        return $this->setting('tooltips', 'titleAlign', $value);
    }

    public function setTooltipsTitleFontColor(string $value)
    {
        return $this->setting('tooltips', 'titleFontColor', $value);
    }

    public function setTooltipsTitleFontSize(string $value)
    {
        return $this->setting('tooltips', 'titleFontSize', $value);
    }

    public function setTooltipsTitleFontStyle(string $value)
    {
        return $this->setting('tooltips', 'titleFontStyle', $value);
    }

    public function setTooltipsTitleMarginBottom(string $value)
    {
        return $this->setting('tooltips', 'titleMarginBottom', $value);
    }

    public function setTooltipsTitleSpacing(string $value)
    {
        return $this->setting('tooltips', 'titleSpacing', $value);
    }

    public function setTooltipsXPadding(string $value)
    {
        return $this->setting('tooltips', 'xPadding', $value);
    }

    public function setTooltipsYPadding(string $value)
    {
        return $this->setting('tooltips', 'yPadding', $value);
    }

    /*
     * Customs
     */

    public function setCustoms(array $values)
    {
        return $this->customs = $values;
    }

    /*
     * Renders
     * */

    public function renderCanvas($width = null, $height = null)
    {
    
        $this->setDimensions($width, $height);
        $canvas_id = !$this->canvas_id ? 'id'.uniqid() : $this->canvas_id;
        $json = $this->renderJson(true);

        if (!isset($this->animation['duration']) || $this->animation['duration'] != 0) {
            $timeout_duration = static::$timeout_duration;
            static::$timeout_duration += 80;
        } else {
            $timeout_duration = 0;
        }

        if ($this->width) {
            $width = 'width="'.$this->width.'"';
        }
        if ($this->height) {
            $height = 'height="'.$this->height.'"';
        }
        $styles = '';
        if (count($this->canvasStyles)) {
            $styles = 'styles="'.implode(';', $this->canvasStyles).'"';
        }


        $return['canvas'] = '
        <canvas '.$width.' '.$height.' id="'.$canvas_id.'" '.$styles.' class="'.$this->canvasClasses.'"></canvas>  

        '.($this->experimental_legend ? '<div id="js-legend'.$canvas_id.'" class="chart-legend"></div> ' : '');

        $return['js'] = ' 
        <script type="application/javascript">  
        $(document).ready(function(){
            if(typeof(JSONfn) === "undefined"){  
             console.log("You must install JSONfn in order to use callback functions. https://github.com/vkiryukhin/jsonfn");
         } 
         
         
         
         
        var json'.$canvas_id.' = '.$json.';
        json'.$canvas_id.' = JSONfn.stringify(json'.$canvas_id.');
        json'.$canvas_id.' = JSONfn.parse(json'.$canvas_id.'); 
        
        
        if('.($this->debug_mode ? 1 : 0).' == 1) console.log(json'.$canvas_id.');  
        
         
            setTimeout(function(){
                var myChart = new Chart($("#'.$canvas_id.'"),json'.$canvas_id.');
                '.$canvas_id.' = myChart; 
                
                if('.($this->experimental_legend ? 1 : 0).' == 1){
                document.getElementById("js-legend'.$canvas_id.'").innerHTML = myChart.generateLegend();
                
                $("#js-legend'.$canvas_id.' > ul > li").on("click", function (e) {
                    var index = $(this).index();
                    $(this).toggleClass("strike");
                    
                    var curr = myChart.data.datasets[0]._meta[myChart.id].data[index];
    
                    curr.hidden = !curr.hidden;
                    myChart.update();
                });
                
                }
                
                }, '.$timeout_duration.');  
            });
         
        
        </script>
        ';
        $this->canvasId(null);

        if (!$this->separate_canvas_and_js) {
            $return = implode(' ', $return);
        }

        return $return;
    }

    public function renderJson($escape_quotes = false)
    {
        //if($this->type == 'scatter') dd(json_encode($this->renderArray(), $escape_quotes ? JSON_HEX_QUOT : 0));

        return json_encode($this->renderArray(), $escape_quotes ? JSON_HEX_QUOT : 0);
    }

    public function renderArray()
    {

        $array['type'] = $this->getType();
        if (!$this->getXLabels() && !$this->getYLabels()) {
            $array['data']['labels'] = $this->getLabels();
        }
        if ($this->getXLabels()) {
            $array['data']['xLabels'] = $this->getXLabels();
        }
        if ($this->getYLabels()) {
            $array['data']['yLabels'] = $this->getYLabels();
        }

        $array['data']['datasets'] = $this->getDatasets();

        $array['data'] = array_merge($array['data'], $this->getCustoms());

        $array['options'] = $this->getOptions();
        $array['options']['animation'] = $this->getAnimation();

        $array['options']['elements'] = $this->getElements();
        $array['options']['events'] = $this->getEvents();
        $array['options']['hover'] = $this->getHover();
        if (count($this->getLayout()) > 0) {
            $array['options']['layout'] = $this->getLayout();
        }
        $array['options']['legend'] = $this->getLegend();
        $array['options']['plugins'] = $this->getPlugins();
        $array['options']['title'] = $this->getTitle();

        if ($this->show_axes) {
            $array['options']['scales'] = $this->getScales();
        }
        $array['options']['tooltips'] = $this->getTooltips();

        $array = $this->convertFunctions($array);

        return $array;
    }
}
