<?php
namespace Tests;

use Exception;
use Metamorphosis\Config;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Runner;
use RdKafka\Message as KafkaMessage;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\Dummies\MiddlewareDummy;

class RunnerTest extends LaravelTestCase
{
    /**
     * Counter for mocking infinite loop.
     */
    protected $messageCount = 0;

    /** @test */
    public function it_should_run()
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
        $config = new Config($topicKey, $consumerGroup);

        $middleware = $this->createMock(MiddlewareDummy::class);
        $this->app->instance(MiddlewareDummy::class, $middleware);

        $consumerInterface = $this->createMock(ConsumerInterface::class);
        $runner = new Runner();
        $runner->setTimeout(30);

        $consumerInterface->expects($this->exactly(4))
            ->method('consume')
            ->with($this->equalTo(30))
            ->will($this->returnCallback([$this, 'consumeMockDataProvider']));

        // Ensure that one message went through the middleware stack
        $middleware->expects($this->once())
            ->method('process');

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
