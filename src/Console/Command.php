<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;

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
        $consumer = new Consumer($this->argument('topic'), $this->argument('consumer-group'));

        if ($timeout = $this->option('timeout')) {
            $consumer->setTimeout($timeout);
        }

        if ($offset = $this->option('offset')) {
            $consumer->setOffset($offset);
        }

        $consumer->consume();
    }
}
