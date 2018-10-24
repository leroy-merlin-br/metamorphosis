<?php
namespace Tests\Config;

use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Config\Consumer;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use Metamorphosis\Exceptions\ConfigurationException;
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

    /** @test */
    public function it_parses_configuration_from_file()
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

    /** @test */
    public function it_gets_default_consumer_group_when_none_is_passed()
    {
        $topicKey = 'topic-key';
        $config = new Consumer($topicKey);

        $this->assertSame('default', $config->getConsumerGroupId());
    }

    /** @test */
    public function it_gets_single_consumer_group_defined()
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

    /** @test */
    public function it_throws_an_exception_when_consumer_group_is_invalid()
    {
        $topicKey = 'topic-key';
        $consumerGroup = 'invalid-consumer-id';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Consumer group 'invalid-consumer-id' not found");

        new Consumer($topicKey, $consumerGroup);
    }

    /** @test */
    public function it_throws_exception_when_overriding_broker_with_invalid_broker()
    {
        $topicKey = 'topic-key';
        $broker = 'invalid_broker';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Broker 'invalid_broker' configuration not found");

        new Consumer($topicKey, null, null, null, $broker);
    }

    /** @test */
    public function it_overrides_broker_from_config_when_passed_by_constructor()
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
}
