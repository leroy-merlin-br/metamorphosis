<?php
namespace Tests\Unit;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema as AvroSchemaConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Metamorphosis\TopicHandler\Producer\AbstractProducer;
use Mockery as m;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function testItShouldProduceRecordAsArrayThroughMiddlewareQueue(): void
    {
        // Set
        $record = ['message' => 'some message'];
        $topic = 'some_topic';

        $producerMiddleware = $this->instance(
            ProducerMiddleware::class,
            m::mock(ProducerMiddleware::class)
        );
        $config = m::mock(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            $topic,
            $broker
        );
        $producerHandler = new class($record, $producerConfigOptions, $topic) extends AbstractProducer {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler, $producerConfigOptions)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic($topic)
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

        $config = m::mock(Config::class);
        $connector = m::mock(Connector::class);

        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            $topic,
            $broker
        );

        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $producerConfigOptions, $topic) extends AbstractProducer {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler, $producerConfigOptions)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic($topic)
            ->andReturn($producerTopic);

        $producerMiddleware->expects()
            ->process()
            ->withAnyArgs();

        // Actions
        $producer->produce($producerHandler);
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
        $config = m::mock(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            $topic,
            $broker,
            0,
            new AvroSchemaConfigOptions('string'),
            [],
            1000,
            false,
            true,
            500,
            1
        );
        $producerHandler = new class($record, $producerConfigOptions, $topic) extends AbstractProducer {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler, $producerConfigOptions)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic($topic)
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

        $config = m::mock(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            $topic,
            $broker,
            null,
            new AvroSchemaConfigOptions('string'),
            [],
            1000,
            true,
            true,
            500,
            1
        );

        $producerHandler = new class($record, $producerConfigOptions, $topic) extends AbstractProducer {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler, $producerConfigOptions)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic($topic)
            ->andReturn($producerTopic);

        $kafkaProducer->expects()
            ->poll(1000)
            ->andReturn(0);

        // Actions
        $result = $producer->build($producerHandler);

        // Assertions
        $this->assertInstanceOf(Dispatcher::class, $result);
    }

    public function testShouldBuildDispatcherWithConfigOptions(): void
    {
        // Set
        $record = json_encode(['message' => 'some message']);
        $topic = 'TOPIC-ID';

        $config = m::mock(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            $topic,
            $broker,
            null,
            new AvroSchemaConfigOptions('string'),
            [],
            1000,
            true,
            true,
            500,
            1
        );
        $producerHandler = new class($record, $producerConfigOptions, $topic) extends AbstractProducer {
        };

        // Expectations
        $connector->expects()
            ->getProducerTopic($producerHandler, $producerConfigOptions)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic($topic)
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
