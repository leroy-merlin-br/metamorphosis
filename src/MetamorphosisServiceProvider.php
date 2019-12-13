<?php
namespace Metamorphosis;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Metamorphosis\Console\ConsumerCommand;
use Metamorphosis\Console\ConsumerMakeCommand;
use Metamorphosis\Console\MiddlewareMakeCommand;
use Metamorphosis\Console\ProducerMakeCommand;

class MetamorphosisServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/kafka.php' => config_path('kafka.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/kafka.php', 'kafka');
    }

    public function register()
    {
        $this->commands([
            ConsumerCommand::class,
            ConsumerMakeCommand::class,
            MiddlewareMakeCommand::class,
            ProducerMakeCommand::class,
        ]);

        App::bind('metamorphosis', function () {
            return new Producer();
        });

        $this->app->singleton('manager', function ($app) {
            return new Manager();
        });
    }
}
