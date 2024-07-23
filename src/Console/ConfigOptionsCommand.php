<?php

namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\Runner;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConfigOptions;

class ConfigOptionsCommand extends BaseCommand
{
    /**
     * @var {inheritdoc}
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $name = 'kafka:consume-config-class';

    /**
     * @var {inheritdoc}
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $description = 'Consumes something with a based class config';

    /**
     * @var {inheritdoc}
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $signature = 'kafka:consume-config-class
        {handler : handler.}
        {--times= : Amount of messages to be consumed.}';

    public function handle(): void
    {
        $consumerHandler = app($this->argument('handler'));

        $configOptions = $consumerHandler->getConfigOptions();

        $this->writeStartingConsumer($configOptions);

        $manager = Factory::make($configOptions);

        $runner = app(Runner::class, compact('manager'));
        $runner->run($this->option('times'));
    }

    private function writeStartingConsumer(ConfigOptions $configOptions): void
    {
        $text = 'Starting consumer for topic: ' . $configOptions->getTopicId() . PHP_EOL;
        $text .= ' on consumer group: ' . $configOptions->getConsumerGroup() . PHP_EOL;
        $text .= 'Connecting in ' . $configOptions->getBroker()->getConnections() . PHP_EOL;
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
