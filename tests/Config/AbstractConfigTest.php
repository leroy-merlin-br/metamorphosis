<?php
namespace Tests\Config;

use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Broker;
use Metamorphosis\Config\AbstractConfig;
use Metamorphosis\Exceptions\ConfigurationException;
use Tests\LaravelTestCase;

class AbstractConfigTest extends LaravelTestCase
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

    public function testItParsesConfigurationFromFile()
    {
        $topicKey = 'topic-key';
        $config = new class($topicKey) extends AbstractConfig {
        };

        $this->assertSame('topic-name', $config->getTopic());
        $this->assertInstanceOf(Broker::class, $config->getBrokerConfig());
        $this->assertSame([
            'first_global_middleware',
            'first_topic_middleware',
        ], $config->getMiddlewares());
    }

    public function testItCanHandleBrokerConfigWithoutAuthenticationKey()
    {
        config([
            'kafka.brokers' => [
                'default' => [
                    'connections' => 'https:some-connection.com:8991',
                ],
            ],
        ]);
        $topicKey = 'topic-key';

        $config = new class($topicKey) extends AbstractConfig {
        };
        $broker = $config->getBrokerConfig();

        $this->assertInstanceOf(NoAuthentication::class, $broker->getAuthentication());
    }

    public function testItThrowsAnExceptionWhenTopicKeyIsInvalid()
    {
        $topicKey = 'invalid-topic-key';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Topic 'invalid-topic-key' not found");

        new class($topicKey) extends AbstractConfig {
        };
    }

    public function testItThrowsAnExceptionWhenBrokerIsInvalid()
    {
        config([
            'kafka.topics' => [
                'topic-key' => [
                    'topic' => 'topic-name',
                    'broker' => 'invalid-broker',
                ],
            ],
        ]);
        $topicKey = 'topic-key';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Broker 'invalid-broker' configuration not found");

        new class($topicKey) extends AbstractConfig {
        };
    }

    public function testItCanHandleMultipleConnectionsForSameBrokerAsString()
    {
        config([
            'kafka.brokers' => [
                'default' => [
                    'connections' => 'https:some-connection.com:8991,https:some-connection.com:8992',
                ],
            ],
        ]);
        $topicKey = 'topic-key';

        $config = new class($topicKey) extends AbstractConfig {
        };
        $broker = $config->getBrokerConfig();

        $this->assertSame('https:some-connection.com:8991,https:some-connection.com:8992', $broker->getConnections());
    }

    public function testItCanHandleMultipleConnectionsForSameBrokerAsArray()
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

        $config = new class($topicKey) extends AbstractConfig {
        };
        $broker = $config->getBrokerConfig();

        $this->assertSame('https:some-connection.com:8991,https:some-connection.com:8992', $broker->getConnections());
    }

    public function testItCanHandleNoMiddlewareConfiguration()
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
        $config = new class($topicKey) extends AbstractConfig {
        };

        $this->assertEmpty($config->getMiddlewares());
    }

    public function testItAcceptHighPerformanceConfigurationPerTopic()
    {
        config([
            'kafka.topics' => [
                'topic-high-performance-enabled-active' => [
                    'topic' => 'topic-name',
                    'broker' => 'default',
                ],
                'topic-high-performance-enable-passive' => [
                    'topic' => 'topic-name',
                    'broker' => 'default',
                    'high-performance' => true,
                ],
                'topic-high-performance-inactive' => [
                    'topic' => 'topic-name',
                    'broker' => 'default',
                    'high-performance' => false,
                ],
            ],
        ]);

        $topicKey = 'topic-high-performance-enabled-active';
        $configEnabledActive = new class($topicKey) extends AbstractConfig {
        };

        $topicKey = 'topic-high-performance-enable-passive';
        $configEnabledPassive = new class($topicKey) extends AbstractConfig {
        };

        $topicKey = 'topic-high-performance-inactive';
        $configDisabled = new class($topicKey) extends AbstractConfig {
        };

        $this->assertTrue($configEnabledActive->isHighPerformanceEnabled());
        $this->assertTrue($configEnabledPassive->isHighPerformanceEnabled());
        $this->assertFalse($configDisabled->isHighPerformanceEnabled());
    }
}
