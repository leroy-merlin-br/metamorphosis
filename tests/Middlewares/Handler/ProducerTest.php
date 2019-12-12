<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use Mockery as m;
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
                        'connections' => '',
                        'auth' => [],
                    ],
                ],
                'topics' => [
                    'topic_key' => [
                        'topic_id' => 'topic_name',
                        'broker' => 'default',
                    ],
                ],
            ],
        ]);
    }

    public function testItShouldProcess(): void
    {
        // Set
        $config = m::mock(Config::class);
        $handler = m::mock(HandlerInterface::class);
        $middlewareHandler = m::mock(MiddlewareHandlerInterface::class);
        $producerTopic = m::mock(ProducerTopic::class);
        $connector = $this->app->instance(Connector::class, m::mock(Connector::class));

        $producerHandler = new Producer($connector, $config);
        $producerHandler->setProducerHandler($handler);

        $record = json_encode(['message' => 'original record']);
        $record = new ProducerRecord($record, 'topic_key');

        // Expectations
        $config->expects()
            ->setOption('topic_key');

        $connector->expects()
            ->setHandler($handler);

        $connector->expects()
            ->getProducerTopic()
            ->andReturn($producerTopic);

        $connector->expects()
            ->handleResponsesFromBroker();

        $producerTopic->expects()
            ->produce(null, 0, $record->getPayload(), null);

        // Actions
        $producerHandler->process($record, $middlewareHandler);
    }
}
