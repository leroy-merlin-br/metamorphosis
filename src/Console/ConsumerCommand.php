<?php

namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\Runner;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer;

class ConsumerCommand extends BaseCommand
{
    /**
     * @var {inheritdoc}
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $name = 'kafka:consume';

    /**
     * @var {inheritdoc}
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $description = 'Consumes something';

    /**
     * @var {inheritdoc}
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $signature = 'kafka:consume
        {topic : topic.}
        {consumer_group? : consumer group name.}
        {--offset= : Sets the offset at which to start consumption.}
        {--partition= : Sets the partition to consume.}
        {--broker= : Override broker connection from config.}
        {--timeout= : Sets timeout for consumer.}
        {--times= : Amount of messages to be consumed.}
        {--config_name= : Change default name for laravel config file.}
        {--service_name= : Change default name for services config file.}';

    public function handle(Config $config)
    {
        $consumer = $config->make($this->option(), $this->argument());

        $this->writeStartingConsumer($consumer);

        $manager = Factory::make($consumer);

        $runner = app(Runner::class, compact('manager'));
        $runner->run($this->option('times'));
    }

    private function writeStartingConsumer(Consumer $consumer)
    {
        $text = 'Starting consumer for topic: '.$consumer->getTopicId().PHP_EOL;
        $text .= ' on consumer group: '.$consumer->getConsumerGroup().PHP_EOL;
        $text .= 'Connecting in '.$consumer->getBroker()->getConnections().PHP_EOL;
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
