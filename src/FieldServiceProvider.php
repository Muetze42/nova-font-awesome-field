<?php

namespace NormanHuth\FontAwesomeField;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/nova-font-awesome-field.php' => config_path('nova-font-awesome-field.php'),
        ], 'config');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('font-awesome-field', __DIR__.'/../dist/js/field.js');
            Nova::style('font-awesome-field', __DIR__.'/../dist/css/field.css');
        });

        if ($this->app->runningInConsole()) {
            $this->commands($this->getCommands());
        }
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/nova-font-awesome-field')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/nova-font-awesome-field.php', 'nova-font-awesome-field'
        );
    }

    /**
     * Get all package commands
     *
     * @return array
     */
    protected function getCommands(): array
    {
        return array_filter(array_map(function ($item) {
            return '\\'.__NAMESPACE__.'\\Console\\Commands\\'.pathinfo($item, PATHINFO_FILENAME);
        }, glob(__DIR__.'/Console/Commands/*.php')), function ($item) {
            return class_basename($item) != 'Command';
        });
    }
}
