<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Facades\Manager;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic as KafkaTopicProducer;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use Mockery as m;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        Manager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
        ]);
    }

    public function testItShouldProcess(): void
    {
        // Set
        $handler = m::mock(HandlerInterface::class);
        $middlewareHandler = m::mock(MiddlewareHandlerInterface::class);
        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(KafkaTopicProducer::class);
        $connector = m::mock(Connector::class);

        $record = json_encode(['message' => 'original record']);
        $record = new ProducerRecord($record, 'topic_key');

        // Expectations
        $connector->expects()
            ->getProducerTopic($handler)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic('topic_name')
            ->andReturn($producerTopic);

        $kafkaProducer->expects()
            ->flush(4000);

        $producerTopic->expects()
            ->produce(null, 0, $record->getPayload(), null);

        // Actions
        $producerHandler = new Producer($connector, $handler);
        $producerHandler->process($record, $middlewareHandler);
    }
}
