<?php
namespace Metamorphosis;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Metamorphosis\Console\Command;
use Metamorphosis\Console\ConsumerMakeCommand;
use Metamorphosis\Console\MiddlewareMakeCommand;

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
            Command::class,
            ConsumerMakeCommand::class,
            MiddlewareMakeCommand::class,
        ]);

        App::bind('metamorphosis', function()
        {
            return new Producer();
        });
    }
}
