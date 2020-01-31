<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\Runner;
use Metamorphosis\Facades\ConfigManager;

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
        {--timeout= : Sets timeout for consumer.}
        {--times= : Amount of messages to be consumed.}';

    public function handle(Config $config)
    {
        $config->setOption($this->option(), $this->argument());

        $this->writeStartingConsumer();

        $manager = Factory::make();

        $runner = app(Runner::class, compact('manager'));
        $runner->run(ConfigManager::get('times'));
    }

    private function writeStartingConsumer()
    {
        $text = 'Starting consumer for topic: '.ConfigManager::get('topic').PHP_EOL;
        $text .= ' on consumer group: '.ConfigManager::get('consumer_group').PHP_EOL;
        $text .= 'Connecting in '.ConfigManager::get('connections').PHP_EOL;
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
