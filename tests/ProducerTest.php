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
                    'some-topic' => [
                        'topic' => 'topic-name',
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
        $topic = 'some-topic';

        $producerMiddleware = $this->app->instance(ProducerMiddleware::class, m::mock(ProducerMiddleware::class));
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
        $record = json_encode(['message' => 'some message']);
        $topic = 'some-topic';
        $this->app->instance(ProducerMiddleware::class, $this->createMock(ProducerMiddleware::class));
        $producer = new Producer();
        $producerHandler = new class($record, $topic) extends AbstractHandler {
            public function __construct($record, string $topic = null, ?string $key = null, ?int $partition = null)
            {
                $this->record = $record;
                $this->topic = $topic;
            }
        };

        $this->assertNull($producer->produce($producerHandler));
    }

    public function testItShouldThrowJsonExceptionWhenPassingMalFormattedArray(): void
    {
        $record = ["\xB1\x31"];
        $topic = 'some-topic';
        $this->app->instance(ProducerMiddleware::class, $this->createMock(ProducerMiddleware::class));
        $producer = new Producer();
        $producerHandler = new class($record, $topic) extends AbstractHandler {
            public function __construct($record, string $topic = null, ?string $key = null, ?int $partition = null)
            {
                $this->record = $record;
                $this->topic = $topic;
            }
        };

        $this->expectException(JsonException::class);

        $producer->produce($producerHandler);
    }
}
