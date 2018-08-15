<?php
namespace Tests\Middlewares\Handler;

use Metamorphosis\Contracts\ConsumerTopicHandler;
use Metamorphosis\Message;
use Metamorphosis\Middlewares\Handler\Consumer;
use RdKafka\Message as KafkaMessage;
use Metamorphosis\Contracts\MiddlewareHandler;
use Tests\LaravelTestCase;

class ConsumerTest extends LaravelTestCase
{
    /** @test */
    public function it_should_call_handle_method_of_consumer_topic_handler()
    {
        $consumerTopicHandler = $this->createMock(ConsumerTopicHandler::class);
        $middlewareHandler = $this->createMock(MiddlewareHandler::class);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);
        $middleware = new Consumer($consumerTopicHandler);

        $consumerTopicHandler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($message));

        $middleware->process($message, $middlewareHandler);
    }
}
