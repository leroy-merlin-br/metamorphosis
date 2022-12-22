<?php

namespace Metamorphosis;

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
            __DIR__ . '/../config/kafka.php' => config_path('kafka.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/kafka.php', 'kafka');
    }

    public function register()
    {
        $this->commands([
            ConsumerCommand::class,
            ConsumerMakeCommand::class,
            MiddlewareMakeCommand::class,
            ProducerMakeCommand::class,
        ]);

        $this->app->bind('metamorphosis', function ($app) {
            return $app->make(Producer::class);
        });
    }
}
