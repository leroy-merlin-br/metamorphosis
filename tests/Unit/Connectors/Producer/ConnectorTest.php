<?php
namespace Tests\Unit\Connectors\Producer;

use Metamorphosis\ConfigManager;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use Mockery as m;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    public function testItShouldMakeSetup(): void
    {
        // Set
        $conf = $this->instance(
            Conf::class,
            m::mock(Conf::class)
        );
        $kafkaProducer = $this->instance(
            KafkaProducer::class,
            m::mock(KafkaProducer::class)
        );
        $configManager = m::mock(ConfigManager::class);

        $connector = new Connector();
        $handler = new class('record', 'some_topic') extends AbstractHandler implements HandleableResponseInterface {
            public function success(Message $message): void
            {
            }

            public function failed(Message $message): void
            {
            }
        };

        // Expectations
        $conf->expects()
            ->setDrMsgCb()
            ->withAnyArgs();

        $conf->expects()
            ->set('metadata.broker.list', 0);

        $configManager->expects()
            ->get('connections')
            ->andReturn('kafka:9092');

        $configManager->expects()
            ->get('auth.type')
            ->andReturn('none');

        // Actions
        $result = $connector->getProducerTopic($handler, $configManager);

        // Assertions
        $this->assertInstanceOf(KafkaProducer::class, $result);
    }

    public function testItShouldMakeSetupWithoutHandleResponse(): void
    {
        // Set
        $conf = $this->instance(
            Conf::class,
            m::mock(Conf::class)
        );
        $kafkaProducer = $this->instance(
            KafkaProducer::class,
            m::mock(KafkaProducer::class)
        );
        $configManager = m::mock(ConfigManager::class);

        $connector = new Connector();
        $handler = new class('record', 'some_topic') extends AbstractHandler implements HandlerInterface {
            public function success(Message $message): void
            {
            }

            public function failed(Message $message): void
            {
            }
        };

        // Expectations
        $conf->shouldReceive('setDrMsgCb')
            ->never();

        $conf->expects()
            ->set('metadata.broker.list', 0);

        $configManager->expects()
            ->get('connections')
            ->andReturn('kafka:9092');

        $configManager->expects()
            ->get('auth.type')
            ->andReturn('none');

        // Actions
        $result = $connector->getProducerTopic($handler, $configManager);

        // Assertions
        $this->assertInstanceOf(KafkaProducer::class, $result);
    }
}
