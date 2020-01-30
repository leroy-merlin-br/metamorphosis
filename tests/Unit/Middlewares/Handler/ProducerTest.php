<?php
namespace Tests\Unit\Middlewares\Handler;

use Metamorphosis\Facades\ConfigManager;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Producer\Pool;
use Metamorphosis\Record\ProducerRecord;
use Mockery as m;
use RdKafka\Producer as KafkaTopicProducer;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        ConfigManager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => true,
            'max_pool_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
            'partition' => 0,
        ]);
    }

    public function testItShouldSendMessageToKafkaBroker(): void
    {
        // Set
        $pool = m::mock(Pool::class);
        $middlewareHandler = m::mock(MiddlewareHandlerInterface::class);
        $producerTopic = m::mock(KafkaTopicProducer::class);

        $record = json_encode(['message' => 'original record']);
        $record = new ProducerRecord($record, 'topic_key');

        // Expectations
        $pool->expects()
            ->handleResponse();

        $pool->expects()
            ->flushMessage();

        $producerTopic->expects()
            ->produce(1, 0, $record->getPayload(), null);

        // Actions
        $producerHandler = new Producer($producerTopic, $pool, 1);
        $producerHandler->process($record, $middlewareHandler);
    }
}
