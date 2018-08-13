<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Config;
use Metamorphosis\Consumer;

class Command extends BaseCommand
{
    protected $name = 'kafka:consume';

    protected $description = 'Consumes something';

    protected $signature = 'kafka:consume
        {topic : topic.}
        {consumer-group? : consumer group name.}
        {--offset : Sets offset for consumer}
        {--timeout : Sets timeout for consumer}';

    public function handle()
    {
        $config = new Config($this->argument('topic'), $this->argument('consumer-group'));

        $consumer = new Consumer($config);

        if ($timeout = $this->option('timeout')) {
            $consumer->setTimeout($timeout);
        }

        if ($offset = $this->option('offset')) {
            $consumer->setOffset($offset);
        }

        $consumer->run();
    }
}
