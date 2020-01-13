<?php
namespace Tests\Unit\Middlewares\Handler;

use Metamorphosis\Middlewares\Handler\Consumer;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Record\ConsumerRecord as Record;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class ConsumerTest extends LaravelTestCase
{
    public function testItShouldCallHandleMethodOfConsumerTopicHandler(): void
    {
        // Set
        $consumerTopicHandler = m::mock(ConsumerTopicHandler::class);
        $middlewareHandler = m::mock(MiddlewareHandlerInterface::class);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);
        $middleware = new Consumer($consumerTopicHandler);

        // Expectations
        $consumerTopicHandler->expects()
            ->handle($record);

        // Actions
        $middleware->process($record, $middlewareHandler);
    }
}
