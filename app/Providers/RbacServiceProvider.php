<?php

namespace Masso\Providers;

use Illuminate\Support\ServiceProvider;
use Blade;

class RbacServiceProvider extends ServiceProvider
{

    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        Blade::directive('ifUserIs', function($expression){
            return "<?php if(Auth::check() && Auth::user()->hasRole{$expression}): ?>";
        });
        Blade::directive('ifUserCan', function($expression){
            return "<?php if(Auth::check() && Auth::user()->canDo{$expression}): ?>";
        });
    }
}
