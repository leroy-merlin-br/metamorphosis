<?php
namespace Tests\Console;

use Metamorphosis\Consumers\HighLevel;
use Metamorphosis\Consumers\LowLevel;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Runner;
use RuntimeException;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\LaravelTestCase;

class CommandTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => '',
                        'auth' => [],
                    ],
                ],
                'topics' => [
                    'topic-key' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'consumer-groups' => [
                            'default' => [
                                'offset' => 'initial',
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_calls_command_with_invalid_topic()
    {
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'some-topic',
        ];

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Topic \'some-topic\' not found');

        $this->artisan($command, $parameters);
    }

    /** @test */
    public function it_calls_command_with_offset_without_partition()
    {
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'some-topic',
            '--offset' => 1,
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough options ("partition" is required when "offset" is supplied).');

        $this->artisan($command, $parameters);
    }

    /** @test */
    public function it_calls_with_high_level_consumer()
    {
        $runner = $this->createMock(Runner::class);

        $this->instance(Runner::class, $runner);

        $runner->expects($this->once())
            ->method('run')
            ->with($this->anything(), $this->callback(function ($subject) {
                return $subject instanceof HighLevel;
            }));

        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic-key',
        ];

        $this->artisan($command, $parameters);
    }

    /** @test */
    public function it_calls_with_low_level_consumer()
    {
        $runner = $this->createMock(Runner::class);
        $this->instance(Runner::class, $runner);

        $runner->expects($this->once())
            ->method('run')
            ->with($this->anything(), $this->callback(function ($subject) {
                return $subject instanceof LowLevel;
            }));

        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic-key',
            '--partition' => 1,
            '--offset' => 5,
        ];

        $this->artisan($command, $parameters);
    }

    /** @test */
    public function it_accepts_timeout_when_calling_command()
    {
        $runner = $this->createMock(Runner::class);
        $this->instance(Runner::class, $runner);

        $runner->expects($this->once())
            ->method('run')
            ->with($this->anything(), $this->callback(function ($subject) {
                return $subject instanceof HighLevel;
            }));

        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'topic-key',
            '--timeout' => 1,
        ];

        $this->artisan($command, $parameters);
    }
}
