<?php
namespace Tests\Unit\Middlewares\Handler;

use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\ConsumerRecord as Record;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class DispatcherTest extends LaravelTestCase
{
    public function testItShouldCreateIteratorInstanceAndStartIt(): void
    {
        // Set
        $middleware = m::mock(MiddlewareInterface::class);

        $queue = [$middleware];
        $dispatcher = new Dispatcher($queue);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        // Expectations
        $middleware->expects('process')
            ->withSomeOfArgs($record);

        // Actions
        $dispatcher->handle($record);
    }
}
