<?php

namespace Tests\Unit\Connectors\Producer;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\SaslSsl;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Tests\LaravelTestCase;

class ConfigTest extends LaravelTestCase
{
    public function testShouldValidateProducerConfig(): void
    {
        // Set
        $config = new Config();
        $topicId = 'default';

        $expected = [
            'timeout' => 10000,
            'is_async' => true,
            'required_acknowledgment' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'partition' => -1,
            'topic' => 'default',
            'connections' => env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092'),
            'auth' => [
                'type' => 'ssl',
                'ca' => base_path('storage/ca.pem'),
                'certificate' => base_path('storage/kafka.cert'),
                'key' => base_path('storage/kafka.key'),
            ],
        ];

        // Actions
        $result = $config->makeByTopic($topicId);

        // Assertions
        $this->assertArraySubset($expected, $result->get());
    }

    public function testShouldNotSetRuntimeConfigWhenKafkaConfigIsInvalid(): void
    {
        // Set
        config(['kafka.topics.default.producer.required_acknowledgment' => 3]);
        $config = new Config();
        $topicId = 'default';

        // Actions
        $this->expectException(ConfigurationException::class);
        $result = $config->makeByTopic($topicId);

        // Assertions
        $this->assertEmpty($result->get());
    }

    public function testShouldNotOverrideDefaultParametersWhenConfigIsSet(): void
    {
        // Set
        config(['kafka.topics.default.producer.max_poll_records' => 3000]);
        $config = new Config();
        $topicId = 'default';

        $expected = [
            'timeout' => 10000,
            'is_async' => true,
            'required_acknowledgment' => true,
            'max_poll_records' => 3000,
            'flush_attempts' => 10,
            'topic' => 'default',
            'connections' => env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092'),
            'auth' => [
                'type' => 'ssl',
                'ca' => base_path('storage/ca.pem'),
                'certificate' => base_path('storage/kafka.cert'),
                'key' => base_path('storage/kafka.key'),
            ],
        ];

        // Actions
        $result = $config->makeByTopic($topicId);

        // Assertions
        $this->assertArraySubset($expected, $result->get());
    }

    public function testShouldOverrideDefaultParametersWhenConfigOptionsExists(): void
    {
        // Set
        config(['kafka.topics.default.producer.max_poll_records' => 3000]);
        $config = new Config();
        $broker = new Broker(
            'kafka:9092',
            new SaslSsl('PLAIN', 'USERNAME', 'PASSWORD')
        );
        $configOptions = new ProducerConfigOptions('TOPIC-ID', $broker);

        $expected = [
            'topic_id' => 'TOPIC-ID',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'sasl_ssl',
                'mechanisms' => 'PLAIN',
                'username' => 'USERNAME',
                'password' => 'PASSWORD',
            ],
            'timeout' => 1000,
            'is_async' => true,
            'required_acknowledgment' => false,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
        ];

        // Actions
        $result = $config->make($configOptions);

        // Assertions
        $this->assertArraySubset($expected, $result->get());
    }
}
