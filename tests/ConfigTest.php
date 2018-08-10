<?php
namespace Tests;

use Metamorphosis\Config;
use Metamorphosis\Contracts\ConsumerTopicHandler;
use Metamorphosis\Exceptions\ConfigurationException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
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
        $this->assertSame([
            'broker' => '',
            'auth' => [
                'protocol' => 'ssl',
                'ca' => '/path/to/ca',
                'certificate' => '/path/to/certificate',
                'key' => '/path/to/key',
            ],
        ], $config->getBrokerConfig());
    }

    /** @test */
    public function it_throws_an_exception_when_topic_key_is_invalid()
    {
        $topicKey = 'invalid-topic-key';
        $consumerGroup = 'consumer-id';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Topic 'invalid-topic-key' not found");

        $config = new Config($topicKey, $consumerGroup);
    }

    /** @test */
    public function it_throws_an_exception_when_consumer_group_is_invalid()
    {
        $topicKey = 'topic-key';
        $consumerGroup = 'invalid-consumer-id';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Consumer group 'invalid-consumer-id' not found");

        $config = new Config($topicKey, $consumerGroup);
    }

    /** @test */
    public function it_throws_an_exception_when_broker_is_invalid()
    {
        $topicKey = 'topic-invalid-broker';
        $consumerGroup = 'consumer-id';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Broker 'invalid-broker' configuration not found");

        $config = new Config($topicKey, $consumerGroup);
    }
}
