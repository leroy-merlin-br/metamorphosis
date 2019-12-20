<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Config;
use Kafka\Producer as KafkaProducer;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use Mockery as m;
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
                        'connections' => 'kafka:9092',
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
        $kafkaProducer = $this->instance(
            KafkaProducer::class,
            m::mock(KafkaProducer::class)
        );
        $handler = m::mock(HandlerInterface::class);
        $middlewareHandler = m::mock(MiddlewareHandlerInterface::class);

        $producerHandler = $this->app->make(Producer::class);
        $producerHandler->setProducerHandler($handler);

        $record = json_encode(['message' => 'original record']);
        $record = new ProducerRecord($record, 'topic_key');

        // Expectations
        $kafkaProducer->expects()
            ->send(true);


        // Actions
        $producerHandler->process($record, $middlewareHandler);
    }
}
