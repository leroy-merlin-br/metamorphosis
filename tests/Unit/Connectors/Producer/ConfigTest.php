<?php
namespace Tests\Unit\Connectors\Producer;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Facades\ConfigManager;
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
            'max_pool_records' => 500,
            'flush_attempts' => 10,
            'partition' => -1,
            'broker' => 'default',
            'topic' => 'default',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/kafka.key',
            ],
        ];

        // Actions
        $config->setOption($topicId);

        // Assertions
        $this->assertArraySubset($expected, ConfigManager::get());
    }

    public function testShouldNotSetRuntimeConfigWhenKafkaConfigIsInvalid(): void
    {
        // Set
        config(['kafka.topics.default.producer.required_acknowledgment' => 3]);
        $config = new Config();
        $topicId = 'default';

        // Actions
        $this->expectException(ConfigurationException::class);
        $config->setOption($topicId);

        // Assertions
        $this->assertEmpty(ConfigManager::get());
    }

    public function testShouldNotOverrideDefaultParametersWhenConfigIsSet(): void
    {
        // Set
        config(['kafka.topics.default.producer.max_pool_records' => 3000]);
        $config = new Config();
        $topicId = 'default';

        $expected = [
            'timeout' => 10000,
            'is_async' => true,
            'required_acknowledgment' => true,
            'max_pool_records' => 3000,
            'flush_attempts' => 10,
            'broker' => 'default',
            'topic' => 'default',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/kafka.key',
            ],
        ];

        // Actions
        $config->setOption($topicId);

        // Assertions
        $this->assertArraySubset($expected, ConfigManager::get());
    }
}
