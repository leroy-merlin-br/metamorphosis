<?php
namespace Tests\Config;

use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Broker;
use Metamorphosis\Config\Config;
use Metamorphosis\Exceptions\ConfigurationException;
use Tests\LaravelTestCase;

class ConfigTest extends LaravelTestCase
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
        $config = new class($topicKey) extends Config {
        };

        $this->assertSame('topic-name', $config->getTopic());
        $this->assertInstanceOf(Broker::class, $config->getBrokerConfig());
        $this->assertSame([
            'first_global_middleware',
            'first_topic_middleware',
        ], $config->getMiddlewares());
    }

    /** @test */
    public function it_can_handle_broker_config_without_authentication_key()
    {
        config([
            'kafka.brokers' => [
                'default' => [
                    'connections' => 'https:some-connection.com:8991',
                ],
            ],
        ]);
        $topicKey = 'topic-key';

        $config = new class($topicKey) extends Config {
        };
        $broker = $config->getBrokerConfig();

        $this->assertInstanceOf(NoAuthentication::class, $broker->getAuthentication());
    }

    /** @test */
    public function it_throws_an_exception_when_topic_key_is_invalid()
    {
        $topicKey = 'invalid-topic-key';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Topic 'invalid-topic-key' not found");

        new class($topicKey) extends Config {
        };
    }

    /** @test */
    public function it_throws_an_exception_when_broker_is_invalid()
    {
        config(['kafka.topics' => [
            'topic-key' => [
                'topic' => 'topic-name',
                'broker' => 'invalid-broker',
            ],
        ]]);
        $topicKey = 'topic-key';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Broker 'invalid-broker' configuration not found");

        new class($topicKey) extends Config {
        };
    }

    /** @test */
    public function it_can_handle_multiple_connections_for_same_broker_as_string()
    {
        config([
            'kafka.brokers' => [
                'default' => [
                    'connections' => 'https:some-connection.com:8991,https:some-connection.com:8992',
                ],
            ],
        ]);
        $topicKey = 'topic-key';

        $config = new class($topicKey) extends Config {
        };
        $broker = $config->getBrokerConfig();

        $this->assertSame('https:some-connection.com:8991,https:some-connection.com:8992', $broker->getConnections());
    }

    /** @test */
    public function it_can_handle_multiple_connections_for_same_broker_as_array()
    {
        config([
            'kafka.brokers' => [
                'default' => [
                    'connections' => [
                        'https:some-connection.com:8991',
                        'https:some-connection.com:8992',
                    ],
                ],
            ],
        ]);
        $topicKey = 'topic-key';

        $config = new class($topicKey) extends Config {
        };
        $broker = $config->getBrokerConfig();

        $this->assertSame('https:some-connection.com:8991,https:some-connection.com:8992', $broker->getConnections());
    }

    /** @test */
    public function it_can_handle_no_middleware_configuration()
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
                    ],
                ],
            ],
        ]);

        $topicKey = 'topic-key';
        $config = new class($topicKey) extends Config {
        };

        $this->assertEmpty($config->getMiddlewares());
    }
}
