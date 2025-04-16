<?php

namespace Tests\Unit\Console;

use Illuminate\Console\OutputStyle;
use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Console\ConsumerCommand;
use Metamorphosis\Consumers\Runner;
use Metamorphosis\Exceptions\ConfigurationException;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\ConsumerHandlerDummy;

class ConsumerCommandTest extends LaravelTestCase
{
    public function testItCallsCommandWithInvalidTopic(): void
    {
        // Set
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'some_topic',
        ];

        // Expectations
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Topic \'some_topic\' not found');

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItCallsCommandWithOffsetWithoutPartition(): void
    {
        // Set
        $runner = $this->instance(Runner::class, m::mock(Runner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'some_topic',
            '--offset' => 1,
        ];

        // Expectations
        $runner->expects()
            ->run()
            ->never();

        $this->expectException(ConfigurationException::class);

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItCallsWithHighLevelConsumer(): void
    {
        // Set
        $runner = $this->instance(Runner::class, m::mock(Runner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic_key',
            'consumer_group' => 'default',
            '--times' => 66,
        ];

        // Expectations
        $runner->expects()
            ->run(66)
            ->once();

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItCallsWithLowLevelConsumer(): void
    {
        // Set
        $runner = $this->instance(Runner::class, m::mock(Runner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic_key',
            '--partition' => 1,
            '--offset' => 5,
        ];

        // Expectations
        $runner->expects()
            ->run(null)
            ->once();

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItAcceptsTimeoutWhenCallingCommand(): void
    {
        // Set
        $runner = $this->instance(Runner::class, m::mock(Runner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic_key',
            '--timeout' => 1,
        ];

        // Expectations
        $runner->expects()
            ->run(null)
            ->once();

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItOverridesBrokerConnectionWhenCallingCommand(): void
    {
        // Set
        config([
            'kafka.brokers.some-broker' => [
                'connections' => '',
                'auth' => [],
            ],
        ]);

        $runner = $this->instance(Runner::class, m::mock(Runner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic_key',
            '--timeout' => 1,
            '--broker' => 'some-broker',
        ];

        // Expectations
        $runner->expects()
            ->run(null)
            ->once();

        $this->artisan($command, $parameters);
    }

    public function testHandleSignalWhenConsumerIsNotRunning(): void
    {
        // Set
        $command = new ConsumerCommand();
        $output = m::mock(OutputStyle::class);
        $command->setOutput($output);

        // Expectations
        $output->expects()
            ->writeln('<error>Consumer is not running.</error>', 32)
            ->andReturnNull();

        // Actions
        $command->handleSignal(SIGINT);
    }

    public function testHandleSignalGracefulShutdown(): void
    {
        // Set
        $config = new Config();
        $output = m::mock(OutputStyle::class);
        $command = new ConsumerCommand();

        $command->setOutput($output);

        $inputDefinition = new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED),
            new InputArgument('topic', InputArgument::REQUIRED),
            new InputOption('times'),
        ]);

        $command->setInput(
            new ArrayInput(
                [
                    'command' => 'kafka:consume',
                    'topic' => 'topic_key',
                    '--times' => 1,
                ],
                $inputDefinition
            ),
        );

        // Expectations
        $output->expects()
            ->writeln(
                "Starting consumer for topic: topic_name\n on consumer group: default\nConnecting in test_kafka:6680\nRunning consumer.."
            )
            ->andReturnNull();

        $output->expects()
            ->writeln(
                '<info>Gracefully shutting down the consumer default from topic topic_name at connection test_kafka:6680 with signal 15...</info>',
                32
            )
            ->andReturnNull();

        // Actions
        $command->handle($config);
        $command->handleSignal(SIGTERM);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $connections = env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092');

        config([
            'kafka' => [
                'topics' => [
                    'topic_key' => [
                        'topic_id' => 'topic_name',
                        'consumer' => [
                            'consumer_group' => 'default',
                            'offset_reset' => 'earliest',
                            'handler' => ConsumerHandlerDummy::class,
                            'timeout' => 123,
                            'max_poll_interval_ms' => 300000,
                        ],
                    ],
                ],
            ],
            'service' => [
                'broker' => [
                    'connections' => $connections,
                    'auth' => [],
                ],
            ],
        ]);
    }
}
