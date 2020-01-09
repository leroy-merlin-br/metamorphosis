<?php
namespace Tests\Unit\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use Mockery as m;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic as KafkaTopicProducer;
use RuntimeException;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        Manager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
        ]);
    }

    public function testItShouldSendMessageToKafkaBroker(): void
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
            ->flush(4000)
            ->andReturn(RD_KAFKA_RESP_ERR_NO_ERROR);

        $producerTopic->expects()
            ->produce(null, 0, $record->getPayload(), null);

        // Actions
        $producerHandler = new Producer($connector, $handler);
        $producerHandler->process($record, $middlewareHandler);
    }

    public function testShouldThrowExceptionWhenFlushFailed(): void
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
            ->flush(4000)
            ->times(10)
            ->andReturn('error');

        $producerTopic->expects()
            ->produce(null, 0, $record->getPayload(), null);

        $this->expectException(RuntimeException::class);

        // Actions
        $producerHandler = new Producer($connector, $handler);
        $producerHandler->process($record, $middlewareHandler);
    }

    public function testItShouldSendMessageToKafkaBrokerWithoutAcknowledgment(): void
    {
        // Set
        Manager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => false,
        ]);

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

        $kafkaProducer->shouldReceive('flush')
            ->never();

        $producerTopic->expects()
            ->produce(null, 0, $record->getPayload(), null);

        // Actions
        $producerHandler = new Producer($connector, $handler);
        $producerHandler->process($record, $middlewareHandler);
    }

    public function testItShouldPollBrokerResponseEveryMaxPollRecordsIsReached(): void
    {
        // Set
        Manager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => true,
            'max_poll_records' => 2,
            'flush_attempts' => 1,
            'required_acknowledgment' => false,
        ]);

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

        $kafkaProducer->shouldReceive('flush')
            ->never();

        $producerTopic->expects()
            ->produce(null, 0, $record->getPayload(), null)
            ->twice();

        // Actions
        $producerHandler = new Producer($connector, $handler);
        $producerHandler->process($record, $middlewareHandler);
        $producerHandler->process($record, $middlewareHandler);
    }

    public function testItShouldHandleResponseEveryTimeWhenAsyncModeIsTrue(): void
    {
        // Set
        Manager::set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => false,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
        ]);

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
            ->flush(4000)
            ->times(3)
            ->andReturn(RD_KAFKA_RESP_ERR_NO_ERROR);

        $producerTopic->expects()
            ->produce(null, 0, $record->getPayload(), null)
            ->twice();

        // Actions
        $producerHandler = new Producer($connector, $handler);
        $producerHandler->process($record, $middlewareHandler);
        $producerHandler->process($record, $middlewareHandler);
    }
}
