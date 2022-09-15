<?php
namespace Tests\Unit;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer;
use Metamorphosis\ProducerConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
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
        $configManager = m::mock(ProducerConfigManager::class)->makePartial();
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $config->expects()
            ->makeByTopic($topic)
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
        $configManager = m::mock(ProducerConfigManager::class)->makePartial();
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $config->expects()
            ->makeByTopic($topic)
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
        $configManager = m::mock(ProducerConfigManager::class);
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
            ->makeByTopic($topic)
            ->andReturn($configManager);

        $connector->expects()
            ->getProducerTopic($producerHandler, $configManager)
            ->andReturn($kafkaProducer);

        $kafkaProducer->expects()
            ->newTopic($topic)
            ->andReturn($producerTopic);

        $kafkaProducer->expects()
            ->flush(1000)
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
        $configManager = m::mock(ProducerConfigManager::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {
        };

        // Expectations
        $config->expects()
            ->makeByTopic($topic)
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
            ->flush(1000)
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
        $topicId = 'TOPIC-ID';

        $config = m::mock(Config::class);
        $connector = m::mock(Connector::class);
        $producer = new Producer($config, $connector);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $producerTopic = m::mock(ProducerTopic::class);
        $configManager = m::mock(ProducerConfigManager::class);
        $broker = new Broker('kafka:9092', new None());
        $configOptions = new ProducerConfigOptions($topicId, $broker);
        $producerHandler = new class($record, $configOptions) extends AbstractProducer {
        };

        // Expectations
        $config->expects()
            ->make($configOptions)
            ->andReturn($configManager);

        $configManager->expects()
            ->middlewares()
            ->andReturn([]);

        $configManager->expects()
            ->get('topic_id')
            ->andReturn($topicId);

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
            ->newTopic($topicId)
            ->andReturn($producerTopic);

        $kafkaProducer->expects()
            ->flush(1000)
            ->andReturn(0);

        // Actions
        $result = $producer->build($producerHandler);

        // Assertions
        $this->assertInstanceOf(Dispatcher::class, $result);
    }
}
