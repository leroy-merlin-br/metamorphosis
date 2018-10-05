<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface as ProducerHandler;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function testItShouldProcess()
    {
        // Set
        $connector = $this->createMock(Connector::class);
        $producerHandler = $this->createMock(ProducerHandler::class);
        $this->app->instance(Connector::class, $connector);
        $record = json_encode(['message' => 'original record']);

        $producerHandler = new Producer($connector, $producerHandler);
        $middlewareHandler = $this->createMock(MiddlewareHandlerInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $producerHandler->setProducerHandler($handler);

        $record = new ProducerRecord($record, 'topic-key');

        $this->assertNull($producerHandler->process($record, $middlewareHandler));
    }

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
                    'topic-key' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                    ],
                ],
            ],
        ]);
    }
}
