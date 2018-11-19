<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\ConsumerRecord as Record;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class DispatcherTest extends LaravelTestCase
{
    public function testItShouldCreateIteratorInstanceAndStartIt()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

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
