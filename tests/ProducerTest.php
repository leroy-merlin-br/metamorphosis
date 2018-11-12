<?php
namespace Tests;

use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;

class ProducerTest extends LaravelTestCase
{
    public function testItShouldProduceRecordAsArrayThroughMiddlewareQueue()
    {
        $record = ['message' => 'some message'];
        $topic = 'some-topic';

        $this->app->instance(ProducerMiddleware::class, $this->createMock(ProducerMiddleware::class));
        $producerHandler = new class($record, $topic) extends AbstractHandler {
            public function __construct($record, string $topic = null, ?string $key = null, ?int $partition = null)
            {
                $this->record = $record;
                $this->topic = $topic;
            }
        };
        $producer = new Producer();

        $this->assertNull($producer->produce($producerHandler));
    }

    public function testItShouldProduceRecordAsStringThroughMiddlewareQueue()
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

    public function testItShouldThrowJsonExceptionWhenPassingMalFormattedArray()
    {
        $record = ["\xB1\x31"];
        $topic = 'some-topic';
        $this->app->instance(ProducerMiddleware::class, $this->createMock(ProducerMiddleware::class));
        $producer = new Producer();
        $producerHandler = new class($record, $topic) extends AbstractHandler {
            public function __construct($record, string $topic = null)
            {
                $this->record = $record;
                $this->topic = $topic;
            }
        };

        $this->expectException(JsonException::class);

        $producer->produce($producerHandler);
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
                    'some-topic' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                    ],
                ],
            ],
        ]);
    }
}
