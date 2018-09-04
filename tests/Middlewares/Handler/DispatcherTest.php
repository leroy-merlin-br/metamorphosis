<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Record\ConsumerRecord as Record;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Middleware;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class DispatcherTest extends LaravelTestCase
{
    /** @test */
    public function it_should_create_iterator_instance_and_start_it()
    {
        $middleware = $this->createMock(Middleware::class);

        $queue = [$middleware];
        $dispatcher = new Dispatcher($queue);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $middleware->expects($this->once())
            ->method('process')
            ->with($this->equalTo($record));

        $dispatcher->handle($record);
    }
}
