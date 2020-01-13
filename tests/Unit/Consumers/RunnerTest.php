<?php
namespace Tests\Unit;

use Exception;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\Runner;
use Metamorphosis\Facades\Manager;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\ConsumerHandlerDummy;
use Tests\Unit\Dummies\MiddlewareDummy;

class RunnerTest extends LaravelTestCase
{
    public function testItShouldRun(): void
    {
        // Set
        Manager::set([
            'connections' => 'kafka:2019',
            'topic' => 'topic_key',
            'broker' => 'default',
            'offset_reset' => 'earliest',
            'offset' => 0,
            'timeout' => 30,
            'handler' => ConsumerHandlerDummy::class,
            'middlewares' => [
                MiddlewareDummy::class,
            ],
            'consumer_group' => 'consumer-id',
        ]);

        $middleware = $this->instance(MiddlewareDummy::class, m::mock(MiddlewareDummy::class));
        $consumerInterface = m::mock(ConsumerInterface::class);

        $runner = new Runner($consumerInterface);

        $kafkaMessage1 = new KafkaMessage();
        $kafkaMessage1->payload = 'original message';
        $kafkaMessage1->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $kafkaMessage2 = new KafkaMessage();
        $kafkaMessage2->payload = 'warning message';
        $kafkaMessage2->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        $kafkaMessage3 = new KafkaMessage();
        $kafkaMessage3->payload = 'error message';
        $kafkaMessage3->err = RD_KAFKA_RESP_ERR_INVALID_MSG;

        $messages = [$kafkaMessage1, $kafkaMessage2, $kafkaMessage3];
        $count = 0;

        // Expectations
        $consumerInterface->shouldReceive('consume')
            ->times(4)
            ->andReturnUsing(function () use ($messages, &$count) {
                $message = $messages[$count] ?? null;
                if (!$message) {
                    throw new Exception('Error when consuming.');
                }
                $count++;

                return $message;
            });

        // Ensure that one message went through the middleware stack
        $middleware->shouldReceive('process')
            ->withAnyArgs();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error when consuming.');

        // Actions
        $runner->run();
    }
}
