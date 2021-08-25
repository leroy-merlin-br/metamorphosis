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
        $configManager = m::mock(ConfigManager::class)->makePartial();
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $config->expects()
            ->setOption($topic)
            ->andReturn($configManager);

        $configManager->expects()
            ->middlewares()
            ->andReturn([]);

        $configManager->expects()
            ->get('topic_id')
            ->andReturn($topic);

        $configManager->expects()
            ->get('partition')
            ->andReturn(0);

        $configManager->expects()
            ->get('timeout')
            ->andReturn(1000);

        $connector->expects()
            ->getProducerTopic($producerHandler, $configManager)
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
        $configManager = m::mock(ConfigManager::class)->makePartial();
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $config->expects()
            ->setOption($topic)
            ->andReturn($configManager);

        $configManager->expects()
            ->middlewares()
            ->andReturn([]);

        $configManager->expects()
            ->get('topic_id')
            ->andReturn($topic);

        $configManager->expects()
            ->get('partition')
            ->andReturn(0);

        $connector->expects()
            ->getProducerTopic($producerHandler, $configManager)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic($topic)
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
        $config = m::mock(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);
        $configManager = m::mock(ConfigManager::class);
        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $configManager->expects()
            ->middlewares()
            ->andReturn([]);

        $configManager->expects()
            ->get('topic_id')
            ->andReturn($topic);

        $configManager->expects()
            ->get('partition')
            ->andReturn(0);

        $configManager->expects()
            ->get('max_poll_records')
            ->andReturn(500);

        $configManager->expects()
            ->get('required_acknowledgment')
            ->andReturn(true);

        $configManager->expects()
            ->get('flush_attempts')
            ->andReturn(1);

        $configManager->expects()
            ->get('timeout')
            ->andReturn(1000);

        $configManager->expects()
            ->get('is_async')
            ->andReturn(false);

        $config->expects()
            ->setOption($topic)
            ->andReturn($configManager);

        $connector->expects()
            ->getProducerTopic($producerHandler, $configManager)
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
        $configManager = m::mock(ConfigManager::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $config->expects()
            ->setOption($topic)
            ->andReturn($configManager);

        $configManager->expects()
            ->middlewares()
            ->andReturn([]);

        $configManager->expects()
            ->get('topic_id')
            ->andReturn($topic);

        $configManager->expects()
            ->get('partition')
            ->andReturn(0);

        $configManager->expects()
            ->get('max_poll_records')
            ->andReturn(500);

        $configManager->expects()
            ->get('is_async')
            ->andReturn(true);

        $configManager->expects()
            ->get('required_acknowledgment')
            ->andReturn(true);

        $configManager->expects()
            ->get('flush_attempts')
            ->andReturn(1);

        $configManager->expects()
            ->get('timeout')
            ->andReturn(1000);

        $connector->expects()
            ->getProducerTopic($producerHandler, $configManager)
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
