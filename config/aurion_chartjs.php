<?php

/*
 * useCallbackFunctions : JSONfn is needed to use callback functions ( https://github.com/vkiryukhin/jsonfn )
 * */
return [
    'default' => [
        'defaultChartsColors' => ["#2c3e50", "#16a085", "#8e44ad", "#3498db"],

        'use_same_color_background_and_border' => false,

        'separate_canvas_and_js' => true,
    ],

    'options' => [

        'width' => null,
        'height' => null,

        'type' => 'bar',
        'showAxes' => true,

        'general' => [
            'decimal' => 2,
            'defaultColor' => "rgba(0,0,0,0.1)",
            'defaultFontColor' => "#666",
            'defaultFontFamily' => '"Proxima Nova W01", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
            'defaultFontSize' => 12,
            'defaultFontStyle' => "normal",
            'maintainAspectRatio' => true,
            'responsive' => true,
            'responsiveAnimationDuration' => 0,
        ],

        'animation' => [
            'duration' => 0, //1500
            'easing' => 'easeOutQuart',
            'onComplete' => null,
            'onProgress' => null,
        ],

        'elements' => [
            'arc' => [
                'backgroundColor' => 'rgba(0,0,0,0.1)',
                'borderColor' => '#fff',
                'borderWidth' => 2,
            ],
            'line' => [
                'backgroundColor' => 'rgba(0,0,0,0.1)',
                'borderCapStyle' => 'butt',
                'borderColor' => 'rgba(0,0,0,0.1)',
                'borderDash' => [],
                'borderDashOffset' => 0,
                'borderJoinStyle' => 'miter',
                'borderWidth' => 3,
                'capBezierPoints' => true,
                'fill' => true,
                'tension' => 0.4,
            ],
            'point' => [
                'backgroundColor' => 'rgba(0,0,0,0.1)',
                'borderColor' => '#fff',
                'borderWidth' => 1,
                'hitRadius' => 1,
                'hoverBorderWidth' => 1,
                'hoverRadius' => 4,
                'pointStyle' => 'circle',
                'radius' => 3,
            ],
            'rectangle' => [
                'backgroundColor' => 'rgba(0,0,0,0.1)',
                'borderColor' => '#fff',
                'borderSkipped' => 'bottom',
                'borderWidth' => 0,
            ],
        ],

        'events' => [
            'mousemove',
            'mouseout',
            'click',
            'touchstart',
            'touchmove',
        ],

        'hover' => [
            'animationDuration' => 400,
            'intersect' => true,
            'mode' => "label",
            'onHover' => null,
        ],

        'legend' => [
            'display' => true,
            'fullWidth' => true,
            'labels' => [
                'boxWidth' => 40,
                'generateLabels' => null,
                'padding' => 10,
            ],
            'position' => 'bottom',
            'onClick' => null,
            'onHover' => null,
            'reverse' => false,
            'weight' => 1000,
        ],

        'plugins' => [
            'filler' => [
                'propagate' => true,
            ],
        ],

        'scales' => [
            'xAxes' => [
                ['stacked' => true, 'ticks' => ['fontSize' => 12]], //false
            ],
            'yAxes' => [
                ['stacked' => true], //false
            ],
            'showLines' => true,
        ],

        'title' => [
            'display' => false,
            'fontStyle' => "bold",
            'fullWidth' => true,
            'padding' => 10,
            'position' => "top",
            'text' => "",
            'weight' => 2000,
        ],

        'tooltips' => [
            'backgroundColor' => "rgba(0,0,0,0.8)",
            'bodyAlign' => "left",
            'bodyFontColor' => "#fff",
            'bodyFontSize' => 12,
            'bodySpacing' => 2,
            'borderColor' => "rgba(0,0,0,0)",
            'borderWidth' => 0,
            'callbacks' => [
                'afterBody' => null,
                'afterFooter' => null,
                'afterLabel' => null,
                'afterTitle' => null,
                'beforeBody' => null,
                'beforeFooter' => null,
                'beforeLabel' => null,
                'beforeTitle' => null,
                'footer' => null,
                'label' => 'labelDefault', //null
                'labelColor' => null,
                'title' => 'titleDefault', //null
            ],
            'events' => [
                'mousemove',
                'touchstart',
                'touchmove',
            ],
            'caretPadding' => 2,
            'caretSize' => 2,
            'cornerRadius' => 6,
            'custom' => null,
            'displayColors' => false,
            'enabled' => true,
            'footerAlign' => "left",
            'footerFontColor' => "#fff",
            'footerFontStyle' => "bold",
            'footerMarginTop' => 6,
            'footerSpacing' => 2,
            'intersect' => true,
            'mode' => "nearest",
            'multiKeyBackground' => "#fff",
            'position' => "average",
            'titleAlign' => "left",
            'titleFontColor' => "#fff",
            'titleFontSize' => 12,
            'titleFontStyle' => "bold",
            'titleMarginBottom' => 5,
            'titleSpacing' => 2,
            'xPadding' => 5,
            'yPadding' => 5,
        ],
    ],

];