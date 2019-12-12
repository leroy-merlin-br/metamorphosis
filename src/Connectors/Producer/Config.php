<?php
namespace Metamorphosis\Connectors\Producer;

use Illuminate\Support\Facades\Validator;
use Metamorphosis\Exceptions\ConfigurationException;

class Config
{
    /**
     * @var array
     */
    protected $rules = [
        'topic' => 'required',
        'broker' => 'required',
        'connections' => 'required|string',
        'timeout' => 'int',
        'auth' => 'array',
        'middlewares' => 'array',
    ];

    public function setOptionConfig(string $topicId): void
    {
        $topicConfig = $this->getTopicConfig($topicId);
        $topicConfig['middlewares'] = array_merge(
            config('kafka.middlewares.producer', []),
            $topicConfig['middlewares'] ?? []
        );
        $brokerConfig = $this->getBrokerConfig($topicConfig['broker']);
        $config = array_merge($topicConfig, $brokerConfig);

        $this->validateConfig($config);
        $this->setConfigRuntime($config);
    }

    private function getTopicConfig(string $topicId): array
    {
        $topicConfig = config('kafka.topics.'.$topicId);
        if (!$topicConfig) {
            throw new ConfigurationException("Topic '{$topicId}' not found");
        }
        $topicConfig['topic'] = $topicId;

        return $topicConfig;
    }

    private function getBrokerConfig(string $brokerId): array
    {
        $brokerConfig = config("kafka.brokers.{$brokerId}");
        if (!$brokerConfig) {
            throw new ConfigurationException("Broker '{$brokerId}' configuration not found");
        }

        return $brokerConfig;
    }

    private function validateConfig(array $config): void
    {
        $validator = Validator::make($config, $this->rules);
        if (!$validator->errors()->isEmpty()) {
            throw new ConfigurationException($validator->errors()->toJson());
        }
    }

    private function setConfigRuntime(array $config): void
    {
        config(['kafka.runtime' => $config]);
    }
}
