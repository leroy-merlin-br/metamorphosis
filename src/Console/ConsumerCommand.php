<?php

namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\Runner;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer;

class ConsumerCommand extends BaseCommand implements SignalableCommandInterface
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

    private ?Runner $runner = null;
    private ?Consumer $consumer = null;

    /**
     * @psalm-suppress PossiblyUnusedMethod Laravel resolves this command entrypoint at runtime.
     */
    public function handle(Config $config): void
    {
        $this->consumer = $config->make($this->option(), $this->argument());

        $this->writeStartingConsumer();

        $manager = Factory::make($this->consumer);

        $this->runner = app(Runner::class, compact('manager'));
        $this->runner->run($this->option('times'));
    }

    public function getSubscribedSignals(): array
    {
        if (!defined('SIGINT') || !defined('SIGTERM')) {
            return [];
        }

        return [constant('SIGINT'), constant('SIGTERM')];
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        if (null === $this->runner) {
            $this->error('Consumer is not running.');

            return self::FAILURE;
        }

        $this->info(
            "Gracefully shutting down the consumer {$this->consumer?->getConsumerGroup()}" .
            " from topic {$this->consumer?->getTopicId()} at connection {$this->consumer?->getBroker()->getConnections()}" .
            " with signal {$signal}..."
        );

        $this->runner->shutdown();

        return $previousExitCode;
    }

    private function writeStartingConsumer(): void
    {
        $text = 'Starting consumer for topic: ' . $this->consumer?->getTopicId() . PHP_EOL;
        $text .= ' on consumer group: ' . $this->consumer?->getConsumerGroup() . PHP_EOL;
        $text .= 'Connecting in ' . $this->consumer?->getBroker()->getConnections() . PHP_EOL;
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
