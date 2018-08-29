<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Config;
use Metamorphosis\Connectors\Consumer\ConnectorFactory;
use Metamorphosis\Runner;
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

    public function handle(Runner $runner)
    {
        if (!is_null($this->getIntOption('offset')) && is_null($this->getIntOption('partition'))) {
            throw new RuntimeException('Not enough options ("partition" is required when "offset" is supplied).');
        }

        $config = new Config(
            $this->argument('topic'),
            $this->argument('consumer-group'),
            $this->getIntOption('partition'),
            $this->getIntOption('offset')
        );

        $connector = ConnectorFactory::make($config);

        if ($timeout = $this->option('timeout')) {
            $runner->setTimeout($timeout);
        }

        $runner->run($config, $connector->getConsumer());
    }

    protected function getIntOption(string $option): ?int
    {
        return !is_null($this->option($option))
            ? (int) $this->option($option)
            : null;
    }
}
