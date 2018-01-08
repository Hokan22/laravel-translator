<?php
namespace Hokan22\LaravelTranslator\Provider;

use Hokan22\LaravelTranslator\Commands\SearchTranslationsCommand;
use Hokan22\LaravelTranslator\Commands\CacheTranslationCommand;
use Hokan22\LaravelTranslator\Translator;
use Illuminate\Support\ServiceProvider;

class TranslatorProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/translator.php', 'translator');

        $this->app->singleton('Translator', function () {
            return new Translator();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/../config/translator.php' => config_path('translator.php'),
            ],
            'config'
        );

        $this->loadMigrationsFrom(__DIR__.'/../migrations/');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'translator');

        $this->loadRoutesFrom(__DIR__.'/../resources/routes.php');


        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheTranslationCommand::class,
                SearchTranslationsCommand::class,
            ]);
        }
    }
}
