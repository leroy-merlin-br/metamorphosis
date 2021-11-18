<?php
namespace Tests\Unit\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\Handler\Iterator;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\ConsumerRecord as Record;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class IteratorTest extends LaravelTestCase
{
    public function testItShouldProcessCurrentMiddlewareAndAdvanceQueuePointer(): void
    {
        // Set
        $middleware = m::mock(MiddlewareInterface::class);

        $queue = [$middleware];
        $iterator = new Iterator($queue);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        // Expectations
        $middleware->expects()
            ->process($record, m::type(Closure::class));

        // Actions
        $iterator->handle($record);
    }
}
