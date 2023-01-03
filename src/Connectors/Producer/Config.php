<?php

namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Connectors\AbstractConfig;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\ProducerConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;

class Config extends AbstractConfig
{
    /**
     * @var string[]
     */
    protected array $rules = [
        'topic_id' => 'required',
        'connections' => 'required|string',
        'timeout' => 'int',
        'is_async' => 'boolean',
        'required_acknowledgment' => 'boolean',
        'max_poll_records' => 'int',
        'flush_attempts' => 'int',
        'auth' => 'nullable|array',
        'middlewares' => 'array',
        'ssl_verify' => 'boolean',
    ];

    /**
     * @var mixed[]
     */
    protected array $default = [
        'timeout' => 1000,
        'is_async' => true,
        'required_acknowledgment' => true,
        'max_poll_records' => 500,
        'flush_attempts' => 10,
        'partition' => RD_KAFKA_PARTITION_UA,
        'ssl_verify' => false,
    ];

    public function make(ProducerConfigOptions $configOptions): AbstractConfigManager
    {
        $configManager = app(ProducerConfigManager::class);
        $configManager->set($configOptions->toArray());

        return $configManager;
    }

    public function makeByTopic(string $topicId): AbstractConfigManager
    {
        $topicConfig = $this->getTopicConfig($topicId);
        $topicConfig['middlewares'] = array_merge(
            config('kafka.middlewares.producer', []),
            $topicConfig['producer']['middlewares'] ?? []
        );
        $brokerConfig = $this->getBrokerConfig(
            'kafka',
            $topicConfig['broker']
        );
        $schemaConfig = $this->getSchemaConfig('kafka', $topicId);
        $config = array_merge($topicConfig, $brokerConfig, $schemaConfig);

        $this->validate($config);
        $config = array_merge($this->default, $config);

        $configManager = app(ProducerConfigManager::class);
        $configManager->set($config);

        return $configManager;
    }

    private function getTopicConfig(string $topicId): array
    {
        $topicConfig = array_merge(
            config('kafka.topics.' . $topicId, []),
            config('kafka.topics.' . $topicId . '.producer', [])
        );
        if (!$topicConfig) {
            throw new ConfigurationException("Topic '{$topicId}' not found");
        }
        $topicConfig['topic'] = $topicId;

        return $topicConfig;
    }
}
