<?php
namespace Tests;

use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer;

class ProducerTest extends LaravelTestCase
{
    /** @test */
    public function it_should_produce_record_as_array_through_middleware_queue()
    {
        $record = ['message' => 'some message'];
        $topic = 'some-topic';
        $this->app->instance(ProducerMiddleware::class, $this->createMock(ProducerMiddleware::class));
        $producer = new Producer();

        $this->assertNull($producer->produce($record, $topic));
    }

    /** @test */
    public function it_should_produce_record_as_string_through_middleware_queue()
    {
        $record = json_encode(['message' => 'some message']);
        $topic = 'some-topic';
        $this->app->instance(ProducerMiddleware::class, $this->createMock(ProducerMiddleware::class));
        $producer = new Producer();

        $this->assertNull($producer->produce($record, $topic));
    }

    /** @test */
    public function it_should_throw_json_exception_when_passing_mal_formatted_array()
    {
        $record = ["\xB1\x31"];
        $topic = 'some-topic';
        $this->app->instance(ProducerMiddleware::class, $this->createMock(ProducerMiddleware::class));
        $producer = new Producer();

        $this->expectException(JsonException::class);

        $producer->produce($record, $topic);
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
