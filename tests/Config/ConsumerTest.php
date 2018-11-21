<?php
namespace Tests\Config;

use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Config\Consumer;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\LaravelTestCase;

class ConsumerTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => '',
                        'auth' => [
                            'protocol' => 'ssl',
                            'ca' => '/path/to/ca',
                            'certificate' => '/path/to/certificate',
                            'key' => '/path/to/key',
                        ],
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
                                'offset-reset' => 'initial',
                                'offset' => 0,
                                'consumer' => ConsumerHandlerDummy::class,
                                'middlewares' => [
                                    'first_consumer_middleware',
                                ],
                            ],
                        ],
                        'middlewares' => [
                            'first_global_middleware',
                            'first_topic_middleware',
                        ],
                    ],
                ],
                'middlewares' => [
                    'consumer' => [
                        'first_global_middleware',
                        'second_global_middleware',
                    ],
                ],
            ],
        ]);
    }

    public function testItParsesConfigurationFromFile()
    {
        $topicKey = 'topic-key';
        $consumerGroup = 'consumer-id';
        $config = new Consumer($topicKey, $consumerGroup);

        $this->assertSame('consumer-id', $config->getConsumerGroupId());
        $this->assertSame('initial', $config->getConsumerOffsetReset());
        $this->assertSame(0, $config->getConsumerOffset());
        $this->assertInstanceOf(ConsumerTopicHandler::class, $config->getConsumerHandler());
        $this->assertSame([
            'first_global_middleware',
            'second_global_middleware',
            'first_topic_middleware',
            'first_consumer_middleware',
        ], $config->getMiddlewares());
    }

    public function testItGetsDefaultConsumerGroupWhenNoneIsPassed()
    {
        $topicKey = 'topic-key';
        $config = new Consumer($topicKey);

        $this->assertSame('default', $config->getConsumerGroupId());
    }

    public function testItGetsSingleConsumerGroupDefined()
    {
        config([
            'kafka.topics' => [
                'topic-key' => [
                    'topic' => 'topic-name',
                    'broker' => 'default',
                    'consumer-groups' => [
                        'any-name' => [
                            'offset-reset' => 'earliest',
                            'offset' => 0,
                            'consumer' => ConsumerHandlerDummy::class,
                        ],
                    ],
                ],
            ],
        ]);

        $topicKey = 'topic-key';
        $config = new Consumer($topicKey);

        $this->assertSame('any-name', $config->getConsumerGroupId());
    }

    public function testItThrowsAnExceptionWhenConsumerGroupIsInvalid()
    {
        $topicKey = 'topic-key';
        $consumerGroup = 'invalid-consumer-id';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Consumer group 'invalid-consumer-id' not found");

        new Consumer($topicKey, $consumerGroup);
    }

    public function testItThrowsExceptionWhenOverridingBrokerWithInvalidBroker()
    {
        $topicKey = 'topic-key';
        $broker = 'invalid_broker';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Broker 'invalid_broker' configuration not found");

        new Consumer($topicKey, null, null, null, $broker);
    }

    public function testItOverridesBrokerFromConfigWhenPassedByConstructor()
    {
        $topicKey = 'topic-key';
        $broker = 'another-broker';
        $connection = 'some-connection';
        config([
            "kafka.brokers.{$broker}" => [
                'connections' => $connection,
                'auth' => [],
            ],
        ]);

        $config = new Consumer($topicKey, null, null, null, $broker);

        $this->assertSame($connection, $config->getBrokerConfig()->getConnections());
        $this->assertInstanceOf(NoAuthentication::class, $config->getBrokerConfig()->getAuthentication());
    }

    public function testMemoryLimitShouldBeNullByDefault()
    {
        $topicKey = 'topic-key';
        $config = new Consumer($topicKey);

        $this->assertNull($config->getMemoryLimit());
    }

    public function testItSetsMemoryLimit()
    {
        $topicKey = 'topic-key';
        $config = new Consumer($topicKey, null, null, null, null, 256);

        $this->assertSame(256, $config->getMemoryLimit());
    }
}
