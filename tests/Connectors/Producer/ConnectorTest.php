<?php
namespace Tests\Connectors\Producer;

use Exception;
use Metamorphosis\Config\Producer;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use RdKafka\Message;
use RdKafka\ProducerTopic;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    public function testItShouldMakeSetup()
    {
        $config = $this->createMock(Producer::class);

        $config->expects($this->once())
            ->method('isHighPerformanceEnabled')
            ->will($this->returnValue(true));

        $connector = new Connector();

        $producer = $connector->getProducerTopic($config);

        $this->assertInstanceOf(ProducerTopic::class, $producer);
    }

    public function testItShouldMakeSetupWithTopicHandler()
    {
        $config = $this->createMock(Producer::class);

        $handler = new class('record', 'some-topic') extends AbstractHandler implements HandleableResponseInterface {
            public function success(Message $message): void
            {
            }

            public function failed(Message $message): void
            {
            }
        };

        $connector = new Connector();

        $connector->setHandler($handler);

        $producer = $connector->getProducerTopic($config);

        $this->assertInstanceOf(ProducerTopic::class, $producer);
    }

    public function testItShouldHandleResponseFromBroker()
    {
        $config = $this->createMock(Producer::class);

        $handler = new class('record', 'some-topic') extends AbstractHandler implements HandleableResponseInterface {
            public function success(Message $message): void
            {
            }

            public function failed(Message $message): void
            {
            }
        };

        $config->expects($this->once())
            ->method('getTimeoutResponse')
            ->willReturn(50);

        $connector = new Connector();

        $connector->setHandler($handler);

        $producer = $connector->getProducerTopic($config);

        $nullReturn = $connector->handleResponsesFromBroker();

        $this->assertNull($nullReturn);
        $this->assertInstanceOf(ProducerTopic::class, $producer);
    }

    public function testItShouldNotHandleResponseFromBroker()
    {
        $config = $this->createMock(Producer::class);

        $handler = new class('record', 'some-topic') extends AbstractHandler {
            public function success(Message $message): void
            {
            }

            public function failed(Message $message): void
            {
            }
        };

        $connector = new Connector();

        $connector->setHandler($handler);

        $connector->getProducerTopic($config);

        $nullReturn = $connector->handleResponsesFromBroker();

        $this->assertNull($nullReturn);
    }

    public function testItShouldThrowExceptionWhenHandleResponseFromBroker()
    {
        $handler = new class('record', 'some-topic') extends AbstractHandler implements HandleableResponseInterface {
            public function success(Message $message): void
            {
            }

            public function failed(Message $message): void
            {
            }
        };

        $connector = new Connector();

        $connector->setHandler($handler);

        $this->expectException(Exception::class);
        $connector->handleResponsesFromBroker();
    }
}
