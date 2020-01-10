<?php
namespace Tests\Integration;

use Exception;
use Illuminate\Support\Facades\Log;
use Metamorphosis\Facades\Metamorphosis;
use Tests\Integration\Dummies\ProductConsumer;
use Tests\Integration\Dummies\ProductHasChanged;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['kafka.brokers.default.auth' => []]);
    }

    public function testShouldRunProducerWithHighLevelConsumer(): void
    {
        // Set
        config(['kafka.topics.default.consumer_groups.test-consumer-group.handler' => ProductConsumer::class]);
        $record = str_random(10);
        $producer = app(ProductHasChanged::class, compact('record'));

        // Expectations
        Log::shouldReceive('info')
            ->withAnyArgs();

        Log::shouldReceive('alert')
            ->with($record)
            ->twice();

        // Actions
        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);
        $this->artisan('kafka:consume', [
            'topic' => 'default',
            'consumer_group' => 'test-consumer-group',
            '--timeout' => 20000,
            '--times' => 2,
        ]);
    }

    public function testShouldRunProducerWithLowLevelConsumer(): void
    {
        // Set
        config(['kafka.topics' => [
            'low_level' => [
                'topic_id' => 'low_level',
                'broker' => 'default',
                'consumer_groups' => [
                    'test-consumer-group' => [
                        'offset_reset' => 'earliest',
                        'offset' => 0,
                        'handler' => ProductConsumer::class,
                        'timeout' => 20000,
                        'middlewares' => [],
                    ],
                ],
                'required_acknowledgment' => true,
                'is_async' => true,
                'max_poll_records' => 500,
                'flush_attempts' => 10,
                'middlewares' => [],
                'timeout' => 20000,
            ]
        ]]);
        $record = 'first record';
        $otherRecord = 'second record';
        $producer = app(ProductHasChanged::class, compact('record'));
        $producer->topic = 'low_level';

        $producer2 = app(ProductHasChanged::class, ['record' => $otherRecord]);
        $producer2->topic = 'low_level';

        // Expectations
        Log::shouldReceive('info')
            ->withAnyArgs();

        Log::shouldReceive('alert')
            ->with($otherRecord)
            ->twice();

        // Actions
        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer2);
        Metamorphosis::produce($producer2);
        $this->artisan('kafka:consume', [
            'topic' => 'low_level',
            'consumer_group' => 'test-consumer-group',
            '--partition' => 0,
            '--offset' => 2,
            '--timeout' => 20000,
            '--times' => 2,
        ]);
    }
}
