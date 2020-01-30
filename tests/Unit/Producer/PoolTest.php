<?php
namespace Tests\Unit\Producer;

use Metamorphosis\Facades\ConfigManager;
use Metamorphosis\Producer\Pool;
use Mockery as m;
use RdKafka\Producer as KafkaProducer;
use RuntimeException;
use Tests\LaravelTestCase;

class PoolTest extends LaravelTestCase
{
    public function testItShouldHandleMessageWithoutAcknowledgment(): void
    {
        // Set
        ConfigManager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => true,
            'max_pool_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => false,
        ]);
        $kafkaProducer = m::mock(KafkaProducer::class);
        $pool = new Pool($kafkaProducer);

        // Expectations
        $kafkaProducer->shouldReceive('pool')
            ->never();

        // Actions
        $pool->handleResponse();
    }

    public function testShouldThrowExceptionWhenFlushFailed(): void
    {
        // Set
        ConfigManager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => false,
            'max_pool_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
            'partition' => 0,
        ]);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $pool = new Pool($kafkaProducer);

        // Expectations
        $kafkaProducer->expects()
            ->pool(4000)
            ->times(10)
            ->andReturn(1);

        $this->expectException(RuntimeException::class);

        // Actions
        $pool->handleResponse();
    }

    public function testItShouldHandleResponseEveryTimeWhenAsyncModeIsTrue(): void
    {
        // Set
        ConfigManager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => false,
            'max_pool_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
            'partition' => 0,
        ]);
        $kafkaProducer = m::mock(KafkaProducer::class);
        $pool = new Pool($kafkaProducer);

        // Expectations
        $kafkaProducer->expects()
            ->pool(4000)
            ->times(3)
            ->andReturn(0);

        // Actions
        $pool->handleResponse();
        $pool->handleResponse();
        $pool->handleResponse();
    }
}
