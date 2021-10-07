<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\ConfigManager;
use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\Runner;

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
        {--times= : Amount of messages to be consumed.}
        {--config_name= : Change default name for laravel config file.}';

    public function handle(Config $config)
    {
        $configManager = $config->make($this->option(), $this->argument());

        $this->writeStartingConsumer($configManager);

        $manager = Factory::make($configManager);

        $runner = app(Runner::class, compact('manager'));
        $runner->run($configManager->get('times'));
    }

    private function writeStartingConsumer(ConfigManager $configManager)
    {
        $text = 'Starting consumer for topic: '.$configManager->get('topic').PHP_EOL;
        $text .= ' on consumer group: '.$configManager->get('consumer_group').PHP_EOL;
        $text .= 'Connecting in '.$configManager->get('connections').PHP_EOL;
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
