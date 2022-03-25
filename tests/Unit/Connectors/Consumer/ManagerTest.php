<?php
namespace Tests\Unit\Connectors\Consumer;

use Exception;
use Metamorphosis\Connectors\Consumer\Manager;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseTimeoutException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerHandler;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class ManagerTest extends LaravelTestCase
{
    public function testShouldHandleMultiplesMessages(): void
    {
        // Set
        $consumerRecord = $this->instance(ConsumerRecord::class, m::mock(ConsumerRecord::class));

        $consumer = m::mock(ConsumerInterface::class);
        $consumerHandler = m::mock(ConsumerHandler::class);
        $dispatcher = m::mock(Dispatcher::class);

        $runner = new Manager($consumer, $consumerHandler, $dispatcher, true, false);

        $kafkaMessage1 = new KafkaMessage();
        $kafkaMessage1->payload = 'original message 1';
        $kafkaMessage1->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $kafkaMessage2 = new KafkaMessage();
        $kafkaMessage2->payload = 'original message 2';
        $kafkaMessage2->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $kafkaMessage3 = new KafkaMessage();
        $kafkaMessage3->payload = 'original message 3';
        $kafkaMessage3->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $messages = [$kafkaMessage1, $kafkaMessage2, $kafkaMessage3];
        $count = 0;
        $exception = new Exception('Exception occurs when consuming.');

        // Expectations
        $consumer->shouldReceive('consume')
            ->times(4)
            ->andReturnUsing(function () use ($messages, &$count, $exception) {
                $message = $messages[$count] ?? null;
                if (!$message) {
                    throw $exception;
                }
                $count++;

                return $message;
            });

        $consumerHandler->expects()
            ->failed($exception);

        $dispatcher->expects()
            ->handle($consumerRecord)
            ->times(3);

        // Actions
        $runner->handleMessage();
        $runner->handleMessage();
        $runner->handleMessage();
        $runner->handleMessage();
    }

    public function testShouldCallWarningWhenErrorOccurs(): void
    {
        // Set
        $consumer = m::mock(ConsumerInterface::class);
        $consumerHandler = m::mock(ConsumerHandler::class);
        $dispatcher = m::mock(Dispatcher::class);

        $runner = new Manager($consumer, $consumerHandler, $dispatcher, true, false);

        $exception = new ResponseWarningException('Error occurs when consuming.');

        // Expectations
        $consumer->shouldReceive('consume')
            ->andThrow($exception);

        $consumerHandler->expects()
            ->warning($exception);

        $dispatcher->shouldReceive('handle')
            ->never();

        // Actions
        $runner->handleMessage();
    }

    public function testShouldHandleAsyncCommit(): void
    {
        // Set
        $consumerRecord = $this->instance(ConsumerRecord::class, m::mock(ConsumerRecord::class));

        $consumer = m::mock(ConsumerInterface::class);
        $consumerHandler = m::mock(ConsumerHandler::class);
        $dispatcher = m::mock(Dispatcher::class);

        $runner = new Manager($consumer, $consumerHandler, $dispatcher, false, true);

        $kafkaMessage1 = new KafkaMessage();
        $kafkaMessage1->payload = 'original message 1';
        $kafkaMessage1->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $kafkaMessage2 = new KafkaMessage();
        $kafkaMessage2->payload = 'original message 2';
        $kafkaMessage2->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $messages = [$kafkaMessage1, $kafkaMessage2];
        $count = 0;
        $exception = new ResponseTimeoutException('Consume timeout or finished to processed.');

        // Expectations
        $consumer->shouldReceive('consume')
            ->times(3)
            ->andReturnUsing(function () use ($messages, &$count, $exception) {
                $message = $messages[$count] ?? null;
                if (!$message) {
                    throw $exception;
                }
                $count++;

                return $message;
            });

        $consumer->expects()
            ->commitAsync()
            ->twice(2);

        $consumer->expects()
            ->canCommit()
            ->twice(2)
            ->andReturnTrue();

        $dispatcher->expects()
            ->handle($consumerRecord)
            ->times(2);

        $consumerHandler->expects()
            ->finished();

        // Actions
        $runner->handleMessage();
        $runner->handleMessage();
        $runner->handleMessage();
    }
}
