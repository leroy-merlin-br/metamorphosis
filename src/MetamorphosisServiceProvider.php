<?php
namespace Metamorphosis;

use Illuminate\Support\ServiceProvider;
use Metamorphosis\Console\Command;
use Metamorphosis\Console\ConsumerMakeCommand;

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
        $this->app->bind('command.kafka:consume', Command::class);
        $this->app->bind('command.make:kafka-consumer', ConsumerMakeCommand::class);

        $this->commands([
            'command.kafka:consume',
            'command.make:kafka-consumer',
        ]);
    }
}
