<?php
namespace Tests\Connectors\Producer;

use Metamorphosis\Config\Producer;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\TopicHandler\Producer\HandleableResponse;
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

        $handler = new class('record', 'some-topic') extends AbstractHandler implements HandleableResponse {
            public function __construct($record, string $topic, ?string $key = null, ?int $partition = null)
            {
            }

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
}
