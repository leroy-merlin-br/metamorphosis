<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Config\Consumer as ConsumerConfig;
use Metamorphosis\Connectors\Consumer\ConnectorFactory;
use Metamorphosis\ConsumerRunner;
use RuntimeException;

class ConsumerCommand extends BaseCommand
{
    /**
     * @var {inheritdoc}
     */
    protected $name = 'kafka:consume';

    /**
     * @var {inheritdoc}
     */
    protected $description = 'Consumes something';

    /**
     * @var {inheritdoc}
     */
    protected $signature = 'kafka:consume
        {topic : topic.}
        {consumer-group? : consumer group name.}
        {--offset= : Sets the offset at which to start consumption.}
        {--partition= : Sets the partition to consume.}
        {--broker= : Override broker connection from config.}
        {--timeout= : Sets timeout for consumer.}
        {--only= : Run only the specific offset passed.}';

    public function handle(ConsumerRunner $runner)
    {
        if ($this->hasOffset() && !$this->hasPartition()) {
            throw new RuntimeException('Not enough options ("partition" is required when "offset" is supplied).');
        }

        if ($this->option('only')  && (!$this->hasOffset() && !$this->hasPartition())) {
            throw new RuntimeException('Not enough options ("offset" and "partition" is required when "only" is supplied).');
        }

        $config = new ConsumerConfig(
            $this->argument('topic'),
            $this->argument('consumer-group'),
            $this->getIntOption('partition'),
            $this->getIntOption('offset'),
            $this->option('broker')
        );

        $this->writeStartingConsumer($config);

        $connector = ConnectorFactory::make($config);

        $this->writeConnectingBroker($config);

        if ($timeout = $this->option('timeout')) {
            $runner->setTimeout($timeout);
        }

        $this->output->writeln('Running consumer..');

        $runner->times($this->getTimes())
            ->run($config, $connector->getConsumer());
    }

    protected function getIntOption(string $option): ?int
    {
        return !is_null($this->option($option))
            ? (int) $this->option($option)
            : null;
    }

    private function hasOffset(): bool
    {
        return !is_null($this->getIntOption('offset'));
    }

    private function hasPartition(): bool
    {
        return !is_null($this->getIntOption('partition'));
    }

    private function getTimes()
    {
        return $this->option('only') ? 1 : 0;
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
