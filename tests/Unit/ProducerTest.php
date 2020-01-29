<?php
namespace Tests\Unit;

use Metamorphosis\ConfigManager;
use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use Mockery as m;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => 'kafka:9092',
                        'auth' => [],
                    ],
                ],
                'topics' => [
                    'some_topic' => [
                        'topic_id' => 'topic_name',
                        'broker' => 'default',
                    ],
                ],
            ],
        ]);
    }

    public function testItShouldProduceRecordAsArrayThroughMiddlewareQueue(): void
    {
        // Set
        $record = ['message' => 'some message'];
        $topic = 'some_topic';
        $producerMiddleware = $this->instance(
            ProducerMiddleware::class,
            m::mock(ProducerMiddleware::class)
        );
        $config = $this->app->make(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic('topic_name')
            ->andReturn($producerTopic);

        $producerMiddleware->expects()
            ->process()
            ->withAnyArgs();

        // Actions
        $producer->produce($producerHandler);
    }

    public function testItShouldProduceRecordAsStringThroughMiddlewareQueue(): void
    {
        // Set
        $record = json_encode(['message' => 'some message']);
        $topic = 'some_topic';
        $producerMiddleware = $this->instance(
            ProducerMiddleware::class,
            m::mock(ProducerMiddleware::class)
        );
        $config = $this->app->make(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic('topic_name')
            ->andReturn($producerTopic);

        $producerMiddleware->expects()
            ->process()
            ->withAnyArgs();

        // Actions
        $result = $producer->produce($producerHandler);

        // Assertions
        $this->assertNull($result);
    }

    public function testItShouldThrowJsonExceptionWhenPassingMalFormattedArray(): void
    {
        // Set
        $record = ["\xB1\x31"];
        $topic = 'some_topic';
        $producerMiddleware = $this->app->instance(
            ProducerMiddleware::class,
            m::mock(ProducerMiddleware::class)
        );
        $config = $this->app->make(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic('topic_name')
            ->andReturn($producerTopic);

        $kafkaProducer->expects()
            ->poll(1000)
            ->andReturn(0);

        $producerMiddleware->expects()
            ->process()
            ->never();

        $this->expectException(JsonException::class);

        // Actions
        $producer->produce($producerHandler);
    }

    public function testShouldBuildDispatcher(): void
    {
        // Set
        $record = json_encode(['message' => 'some message']);
        $topic = 'some_topic';

        $config = $this->app->make(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic('topic_name')
            ->andReturn($producerTopic);

        $kafkaProducer->expects()
            ->poll(1000)
            ->andReturn(0);

        // Actions
        $result = $producer->build($producerHandler);

        // Assertions
        $this->assertInstanceOf(Dispatcher::class, $result);
    }
}
