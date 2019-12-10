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
    public function testItShouldMakeSetup(): void
    {
        // Set
        $connector = new Connector();

        // Actions
        $result = $connector->getProducerTopic();

        // Assertions
        $this->assertInstanceOf(ProducerTopic::class, $result);
    }

    public function testItShouldMakeSetupWithTopicHandler(): void
    {
        // Set
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

        // Actions
        $result = $connector->getProducerTopic();

        // Assertions
        $this->assertInstanceOf(ProducerTopic::class, $result);
    }

    public function testItShouldHandleResponseFromBroker(): void
    {
        // Set
        config(['kafka.runtime.timeout' => 61]);
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

        $producer = $connector->getProducerTopic();

        // Actions
        $result = $connector->handleResponsesFromBroker();

        // Assertions
        $this->assertNull($result);
        $this->assertInstanceOf(ProducerTopic::class, $producer);
    }

    public function testItShouldNotHandleResponseFromBroker(): void
    {
        // Set
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

        $connector->getProducerTopic();

        // Actions
        $result = $connector->handleResponsesFromBroker();

        // Assertions
        $this->assertNull($result);
    }

    public function testItShouldThrowExceptionWhenHandleResponseFromBroker(): void
    {
        // Set
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

        // Actions
        $connector->handleResponsesFromBroker();
    }
}
