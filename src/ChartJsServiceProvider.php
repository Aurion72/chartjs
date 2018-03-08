<?php

namespace Aurion72\ChartJs;

use Illuminate\Support\ServiceProvider;

class ChartJsServiceProvider extends ServiceProvider
{

    const CONFIG_FOLDER = __DIR__.'/../config/';
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            static::CONFIG_FOLDER.'aurion_chartjs.php' => config_path('aurion_chartjs.php'),
            __DIR__.'/js/chartjs.min.js' => resource_path('assets/js/vendor/aurion_chartjs/chartjs.min.js'),
            __DIR__.'/js/jsonfn.min.js' => resource_path('assets/js/vendor/aurion_chartjs/jsonfn.min.js')
        ],'aurion_chartjs');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(static::CONFIG_FOLDER.'aurion_chartjs.php', 'aurion_chartjs');

        $this->app->bind('aurion_chartjs', function($app, $params){
            $type = null;
            if(isset($params['type'])) $type = $params['type'];
           return new ChartJs($type);
        });
    }
}