<?php
namespace Metamorphosis;

use Illuminate\Support\ServiceProvider;
use Metamorphosis\Console\ConfigOptionsCommand;
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
            __DIR__.'/../config/service.php' => config_path('service.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/kafka.php', 'kafka');
        $this->mergeConfigFrom(__DIR__.'/../config/service.php', 'service');
    }

    public function register()
    {
        $this->commands([
            ConsumerCommand::class,
            ConsumerMakeCommand::class,
            MiddlewareMakeCommand::class,
            ProducerMakeCommand::class,
            ConfigOptionsCommand::class,
        ]);

        $this->app->bind('metamorphosis', function ($app) {
            return $app->make(Producer::class);
        });
    }
}
