<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Middlewares\Handler\Consumer;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Record\ConsumerRecord as Record;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class ConsumerTest extends LaravelTestCase
{
    public function testItShouldCallHandleMethodOfConsumerTopicHandler()
    {
        $consumerTopicHandler = $this->createMock(ConsumerTopicHandler::class);
        $middlewareHandler = $this->createMock(MiddlewareHandlerInterface::class);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);
        $middleware = new Consumer($consumerTopicHandler);

        $consumerTopicHandler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($record));

        $middleware->process($record, $middlewareHandler);
    }
}
