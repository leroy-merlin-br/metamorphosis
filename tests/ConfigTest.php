<?php
namespace Tests;

use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Broker;
use Metamorphosis\Config;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use Metamorphosis\Exceptions\ConfigurationException;
use Tests\Dummies\ConsumerHandlerDummy;

class ConfigTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connection' => '',
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
                                'offset' => 'earliest',
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                            'consumer-id' => [
                                'offset' => 'initial',
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
        $config = new Config($topicKey, $consumerGroup);

        $this->assertSame('topic-name', $config->getTopic());
        $this->assertSame('consumer-id', $config->getConsumerGroupId());
        $this->assertSame('initial', $config->getConsumerGroupOffset());
        $this->assertInstanceOf(ConsumerTopicHandler::class, $config->getConsumerGroupHandler());
        $this->assertInstanceOf(Broker::class, $config->getBrokerConfig());
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
        $config = new Config($topicKey);

        $this->assertSame('default', $config->getConsumerGroupId());
    }

    /** @test */
    public function it_can_handle_broker_config_without_authentication_key()
    {
        config([
            'kafka.brokers' => [
                'default' => [
                    'connection' => 'https:some-connection.com:8991',
                ],
            ],
        ]);
        $topicKey = 'topic-key';

        $config = new Config($topicKey);
        $broker = $config->getBrokerConfig();

        $this->assertInstanceOf(NoAuthentication::class, $broker->getAuthentication());
    }

    /** @test */
    public function it_throws_an_exception_when_topic_key_is_invalid()
    {
        $topicKey = 'invalid-topic-key';
        $consumerGroup = 'consumer-id';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Topic 'invalid-topic-key' not found");

        new Config($topicKey, $consumerGroup);
    }

    /** @test */
    public function it_throws_an_exception_when_consumer_group_is_invalid()
    {
        $topicKey = 'topic-key';
        $consumerGroup = 'invalid-consumer-id';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Consumer group 'invalid-consumer-id' not found");

        new Config($topicKey, $consumerGroup);
    }

    /** @test */
    public function it_throws_an_exception_when_broker_is_invalid()
    {
        config(['kafka.topics' => [
            'topic-key' => [
                'topic' => 'topic-name',
                'broker' => 'invalid-broker',
                'consumer-groups' => [
                    'default' => [
                        'offset' => 'earliest',
                        'consumer' => ConsumerHandlerDummy::class,
                    ],
                ],
            ],
        ]]);
        $topicKey = 'topic-key';
        $consumerGroup = 'default';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Broker 'invalid-broker' configuration not found");

        new Config($topicKey, $consumerGroup);
    }
}
