<?php
namespace Tests;

use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use Mockery as m;

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

        $producerHandler = new class($record, $topic) extends AbstractHandler {
            public function __construct($record, string $topic = null, ?string $key = null, ?int $partition = null)
            {
                $this->record = $record;
                $this->topic = $topic;
            }
        };
        $producer = new Producer();

        // Expectations
        $producerMiddleware->expects()
            ->setProducerHandler($producerHandler);

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
        $producerMiddleware = $this->app->instance(
            ProducerMiddleware::class,
            m::mock(ProducerMiddleware::class)
        );
        $producer = new Producer();
        $producerHandler = new class($record, $topic) extends AbstractHandler {
            public function __construct($record, string $topic = null, ?string $key = null, ?int $partition = null)
            {
                $this->record = $record;
                $this->topic = $topic;
            }
        };

        // Expectations
        $producerMiddleware->expects()
            ->setProducerHandler($producerHandler);

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
        $producer = new Producer();
        $producerHandler = new class($record, $topic) extends AbstractHandler {
            public function __construct($record, string $topic = null, ?string $key = null, ?int $partition = null)
            {
                $this->record = $record;
                $this->topic = $topic;
            }
        };

        // Expectations
        $producerMiddleware->expects()
            ->setProducerHandler($producerHandler);

        $producerMiddleware->expects()
            ->process()
            ->never();

        $this->expectException(JsonException::class);

        // Actions
        $producer->produce($producerHandler);
    }
}
