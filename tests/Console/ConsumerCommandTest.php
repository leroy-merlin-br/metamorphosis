<?php
namespace Tests\Console;

use Metamorphosis\ConsumerRunner;
use Metamorphosis\Exceptions\ConfigurationException;
use Mockery as m;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\LaravelTestCase;

class ConsumerCommandTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => 'test-kafka:6680',
                        'auth' => [],
                    ],
                ],
                'topics' => [
                    'topic-key' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'consumer_groups' => [
                            'default' => [
                                'offset_reset' => 'earliest',
                                'handler' => ConsumerHandlerDummy::class,
                                'timeout' => 123,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testItCallsCommandWithInvalidTopic(): void
    {
        // Set
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'some-topic',
        ];

        // Expectations
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Topic \'some-topic\' not found');

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItCallsCommandWithOffsetWithoutPartition(): void
    {
        // Set
        $runner = $this->instance(ConsumerRunner::class, m::mock(ConsumerRunner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'some-topic',
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
        $runner = $this->instance(ConsumerRunner::class, m::mock(ConsumerRunner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic-key',
            'consumer_group' => 'default',
        ];

        // Expectations
        $runner->expects()
            ->run()
            ->once();

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItCallsWithLowLevelConsumer(): void
    {
        // Set
        $runner = $this->instance(ConsumerRunner::class, m::mock(ConsumerRunner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic-key',
            '--partition' => 1,
            '--offset' => 5,
        ];

        // Expectations
        $runner->expects()
            ->run()
            ->once();

        // Actions
        $this->artisan($command, $parameters);
    }

    public function testItAcceptsTimeoutWhenCallingCommand(): void
    {
        // Set
        $runner = $this->instance(ConsumerRunner::class, m::mock(ConsumerRunner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic-key',
            '--timeout' => 1,
        ];

        // Expectations
        $runner->expects()
            ->run()
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

        $runner = $this->instance(ConsumerRunner::class, m::mock(ConsumerRunner::class));
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic-key',
            '--timeout' => 1,
            '--broker' => 'some-broker',
        ];

        // Expectations
        $runner->expects()
            ->run()
            ->once();

        $this->artisan($command, $parameters);
    }
}
