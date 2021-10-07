<?php
namespace Tests\Unit\Connectors\Producer;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\Producer\ConfigOptions;
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
            'broker' => 'default',
            'topic' => 'default',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.key',
            ],
            'url' => '',
            'request_options' => [],
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
            'broker' => 'default',
            'topic' => 'default',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.key',
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
        $broker = [
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'sasl_ssl',
                'mechanisms' => 'PLAIN',
                'username' => 'USERNAME',
                'password' => 'PASSWORD',
            ],
        ];
        $configOptions = new ConfigOptions('TOPIC-ID', $broker);

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
            'avro_schema' => [],
        ];

        // Actions
        $result = $config->make($configOptions);

        // Assertions
        $this->assertArraySubset($expected, $result->get());
    }
}
