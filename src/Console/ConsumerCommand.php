<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\ConnectorFactory;
use Metamorphosis\ConsumerRunner;
use Metamorphosis\Manager;

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
        {consumer_group? : consumer group name.}
        {--offset= : Sets the offset at which to start consumption.}
        {--partition= : Sets the partition to consume.}
        {--broker= : Override broker connection from config.}
        {--timeout= : Sets timeout for consumer.}';

    public function handle(Config $config)
    {
        $config->setOption($this->option(), $this->argument());

        $this->writeStartingConsumer();

        $consumer = ConnectorFactory::make()->getConsumer();

        $runner = app(ConsumerRunner::class, compact('consumer'));
        $runner->run();
    }

    private function writeStartingConsumer()
    {
        $text = 'Starting consumer for topic: '.Manager::get('topic').PHP_EOL;
        $text .= ' on consumer group: '.Manager::get('consumer_group').PHP_EOL;
        $text .= 'Connecting in '.Manager::get('connections').PHP_EOL;
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
