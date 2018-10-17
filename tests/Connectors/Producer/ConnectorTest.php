<?php
namespace Tests\Connectors\Producer;

use Exception;
use Metamorphosis\Config\Producer;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use RdKafka\Message;
use RdKafka\ProducerTopic;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    /** @test */
    public function it_should_make_setup()
    {
        $config = $this->createMock(Producer::class);

        $connector = new Connector();

        $producer = $connector->getProducerTopic($config);

        $this->assertInstanceOf(ProducerTopic::class, $producer);
    }

    /** @test */
    public function it_should_make_setup_with_topic_handler()
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

    /** @test */
    public function it_should_handle_response_from_broker()
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

    /** @test */
    public function it_should_not_handle_response_from_broker()
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

    /** @test */
    public function it_should_throw_exception_when_handle_response_from_broker()
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
