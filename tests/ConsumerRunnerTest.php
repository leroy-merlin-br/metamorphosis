<?php
namespace Tests;

use Exception;
use Metamorphosis\ConsumerRunner;
use Metamorphosis\Consumers\ConsumerInterface;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\Dummies\MiddlewareDummy;

class ConsumerRunnerTest extends LaravelTestCase
{
    public function testItShouldRun(): void
    {
        // Set
        config([
            'kafka.runtime' => [
                'connections' => 'kafka:2019',
                'topic' => 'topic-key',
                'broker' => 'default',
                'offset_reset' => 'earliest',
                'offset' => 0,
                'timeout' => 30,
                'handler' => ConsumerHandlerDummy::class,
                'middlewares' => [
                    MiddlewareDummy::class,
                ],
                'consumer-group' => 'consumer-id',
            ],
        ]);

        $middleware = $this->instance(MiddlewareDummy::class, m::mock(MiddlewareDummy::class));
        $consumerInterface = m::mock(ConsumerInterface::class);

        $runner = new ConsumerRunner($consumerInterface);

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
