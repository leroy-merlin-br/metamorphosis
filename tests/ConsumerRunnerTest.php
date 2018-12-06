<?php
namespace Tests;

use Exception;
use Metamorphosis\Config\Consumer;
use Metamorphosis\ConsumerRunner;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\MemoryManager;
use RdKafka\Message as KafkaMessage;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\Dummies\MiddlewareDummy;

class ConsumerRunnerTest extends LaravelTestCase
{
    /**
     * @var int Counter for mocking infinite loop.
     */
    protected $messageCount = 0;

    public function testItShouldRun()
    {
        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => '',
                    ],
                ],
                'topics' => [
                    'topic-key' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'consumer-groups' => [
                            'default' => [
                                'offset-reset' => 'earliest',
                                'offset' => 0,
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                            'consumer-id' => [
                                'offset-reset' => 'earliest',
                                'offset' => 0,
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                        ],
                    ],
                ],
                'middlewares' => [
                    'consumer' => [
                        MiddlewareDummy::class,
                    ],
                ],
            ],
        ]);

        $topicKey = 'topic-key';
        $consumerGroup = 'consumer-id';
        $memoryLimit = 128;
        $config = new Consumer($topicKey, $consumerGroup, null, null, null, $memoryLimit);

        $middleware = $this->createMock(MiddlewareDummy::class);
        $this->app->instance(MiddlewareDummy::class, $middleware);

        $consumerInterface = $this->createMock(ConsumerInterface::class);
        $memoryManager = $this->createMock(MemoryManager::class);
        $runner = new ConsumerRunner($memoryManager);
        $runner->setTimeout(30);

        $consumerInterface->expects($this->exactly(4))
            ->method('consume')
            ->with($this->equalTo(30))
            ->will($this->returnCallback([$this, 'consumeMockDataProvider']));

        // Ensure that one message went through the middleware stack
        $middleware->expects($this->once())
            ->method('process');

        $memoryManager->expects($this->exactly(3))
            ->method('memoryExceeded')
            ->with($this->equalTo($memoryLimit))
            ->will($this->returnValue(false));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error when consuming.');

        $runner->run($config, $consumerInterface);
    }

    public function consumeMockDataProvider()
    {
        switch ($this->messageCount++) {
            case 0:
                $kafkaMessage = new KafkaMessage();
                $kafkaMessage->payload = 'original message';
                $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

                return $kafkaMessage;

            case 1:
                $kafkaMessage = new KafkaMessage();
                $kafkaMessage->payload = 'warning message';
                $kafkaMessage->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

                return $kafkaMessage;

            case 2:
                $kafkaMessage = new KafkaMessage();
                $kafkaMessage->payload = 'error message';
                $kafkaMessage->err = RD_KAFKA_RESP_ERR_INVALID_MSG;

                return $kafkaMessage;

            case 3:
                throw new Exception('Error when consuming.');
        }
    }
}
