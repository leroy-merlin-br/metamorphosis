<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Config\Consumer as ConsumerConfig;
use Metamorphosis\Connectors\Consumer\ConnectorFactory;
use Metamorphosis\Runner;
use RuntimeException;

class ConsumerCommand extends BaseCommand
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

        $config = new ConsumerConfig(
            $this->argument('topic'),
            $this->argument('consumer-group'),
            $this->getIntOption('partition'),
            $this->getIntOption('offset')
        );

        $this->writeStartingConsumer($config);

        $connector = ConnectorFactory::make($config);

        $this->writeConnectingBroker($config);

        if ($timeout = $this->option('timeout')) {
            $runner->setTimeout($timeout);
        }

        $this->output->writeln('Running consumer..');
        $runner->run($config, $connector->getConsumer());
    }

    protected function getIntOption(string $option): ?int
    {
        return !is_null($this->option($option))
            ? (int) $this->option($option)
            : null;
    }

    private function writeStartingConsumer(ConsumerConfig $config)
    {
        $text = 'Starting consumer for topic: '.$config->getTopic();
        $text .= ' on consumer group: '.$config->getConsumerGroupId();

        $this->output->writeln($text);
    }

    private function writeConnectingBroker(ConsumerConfig $config)
    {
        $this->output->writeln('Connecting in '.$config->getBrokerConfig()->getConnections());
    }
}
