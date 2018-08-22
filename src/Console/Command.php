<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Config;
use Metamorphosis\Consumer;
use RuntimeException;

class Command extends BaseCommand
{
    protected $name = 'kafka:consume';

    protected $description = 'Consumes something';

    protected $signature = 'kafka:consume
        {topic : topic.}
        {consumer-group? : consumer group name.}
        {--offset= : Sets the offset at which to start consumption.}
        {--partition= : Sets the partition to consume.}
        {--timeout= : Sets timeout for consumer.}';

    public function handle()
    {
        if ($this->option('offset') && is_null($this->getPartition())) {
            throw new RuntimeException('Not enough options ("partition" is required when "offset" is supplied).');
        }

        $config = new Config(
            $this->argument('topic'),
            $this->argument('consumer-group'),
            $this->option('offset'),
            $this->getPartition()
        );

        $connector = ConnectorFactory::make($config);

        $runner = new Runner($config, $connector->getConsumer());

        if ($timeout = $this->option('timeout')) {
            $runner->setTimeout($timeout);
        }

        $runner->run();
    }

    protected function getPartition(): ?int
    {
        return !is_null($this->option('partition')) ? (int) $this->option('partition') : null;
    }
}
