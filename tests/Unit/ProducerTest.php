<?php
namespace Tests\Unit;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
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
                    'some_topic' => [
                        'topic_id' => 'topic_name',
                        'broker' => 'default',
                    ],
                ],
            ],
        ]);
    }

    public function testItShouldProduceRecordAsArrayThroughMiddlewareQueue(): void
    {
        // Set
        $record = ['message' => 'some message'];
        $topic = 'some_topic';
        $producerMiddleware = $this->instance(
            ProducerMiddleware::class,
            m::mock(ProducerMiddleware::class)
        );
        $config = $this->app->make(Config::class);

        $producerHandler = new class($record, $topic) extends AbstractHandler {};
        $producer = new Producer($config);

        // Expectations
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
        $config = $this->app->make(Config::class);
        $producer = new Producer($config);
        $producerHandler = new class($record, $topic) extends AbstractHandler {};

        // Expectations
        $producerMiddleware->expects()
            ->process()
            ->withAnyArgs();

        // Actions
        $result = $producer->produce($producerHandler);

        // Assertions
        $this->assertNull($result);
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
        $config = $this->app->make(Config::class);
        $producer = new Producer($config);
        $producerHandler = new class($record, $topic) extends AbstractHandler {};

        // Expectations
        $producerMiddleware->expects()
            ->process()
            ->never();

        $this->expectException(JsonException::class);

        // Actions
        $producer->produce($producerHandler);
    }

    public function testShouldBuildDispacther(): void
    {
        // Set
        $record = json_encode(['message' => 'some message']);
        $topic = 'some_topic';
        $config = $this->instance(Config::class, m::mock(Config::class));
        $producer = new Producer($config);
        $producerHandler = new class($record, $topic) extends AbstractHandler {};

        // Expectations
        $config->expects()
            ->setOption('some_topic');

        // Actions
        $result = $producer->build($producerHandler);

        // Assertions
        $this->assertInstanceOf(Dispatcher::class, $result);
    }
}
