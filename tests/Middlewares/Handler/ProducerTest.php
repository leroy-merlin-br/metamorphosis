<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\Handler;
use Tests\LaravelTestCase;
use Metamorphosis\TopicHandler\Producer\Handler as ProducerHandler;

class ProducerTest extends LaravelTestCase
{
    /** @test */
    public function it_should_process()
    {
        // Set
        $connector = $this->createMock(Connector::class);
        $producerHandler = $this->createMock(ProducerHandler::class);
        $this->app->instance(Connector::class, $connector);
        $record = json_encode(['message' => 'original record']);

        $producerHandler = new Producer($connector, $producerHandler);
        $middlewareHandler = $this->createMock(MiddlewareHandler::class);
        $handler = $this->createMock(Handler::class);
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
