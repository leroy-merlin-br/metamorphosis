<?php

namespace Tests\Unit\Console;

use Metamorphosis\Consumers\Runner;
use Metamorphosis\Exceptions\ConfigurationException;
use Mockery as m;
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

    protected function setUp(): void
    {
        parent::setUp();

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
                    'connections' => 'test_kafka:6680',
                    'auth' => [],
                ],
            ],
        ]);
    }
}
