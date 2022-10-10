<?php
namespace Tests\Unit\Connectors\Consumer;

use Error;
use Exception;
use InvalidArgumentException;
use Metamorphosis\Connectors\Consumer\Manager;
use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseTimeoutException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerHandler;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\ConsumerHandlerDummy;
use Throwable;
use TypeError;

class ManagerTest extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $configManager = new ConsumerConfigManager();
        $configManager->set([
            'connections' => 'kafka:2019',
            'topic' => 'topic_key',
            'broker' => 'default',
            'offset_reset' => 'earliest',
            'offset' => 0,
            'timeout' => 30,
            'handler' => ConsumerHandlerDummy::class,
            'middlewares' => [],
            'consumer_group' => 'consumer-id',
        ]);
    }

    /**
     * @dataProvider getThrowableScenarios
     */
    public function testShouldHandlerAnyThrowable(Throwable $throwable): void
    {
        // Set
        $consumer = m::mock(ConsumerInterface::class);
        $consumerHandler = m::mock(ConsumerHandler::class);
        $dispatcher = m::mock(Dispatcher::class);

        $runner = new Manager($consumer, $consumerHandler, $dispatcher, true, false);

        // Expectations
        $consumer->expects()
            ->consume()
            ->andThrow($throwable);

        $consumerHandler->expects()
            ->failed($throwable);

        // Actions
        $runner->handleMessage();
    }

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

        // Expectations
        $consumer->shouldReceive()
            ->consume()
            ->times(3)
            ->andReturn($kafkaMessage1, $kafkaMessage2, $kafkaMessage3);

        $dispatcher->expects()
            ->handle($consumerRecord)
            ->times(3);

        // Actions
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

    public function getThrowableScenarios(): array
    {
        return [
            'Exception' => [
                'throwable' => new Exception(),
            ],
            'Error' => [
                'throwable' => new Error(),
            ],
            'InvalidArgumentException' => [
                'throwable' => new InvalidArgumentException(),
            ],
            'TypeError' => [
                'throwable' => new TypeError(),
            ],
        ];
    }
}
