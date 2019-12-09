<?php
namespace Metamorphosis\Config;

use Illuminate\Support\Facades\Validator;
use Metamorphosis\Exceptions\ConfigurationException;

class Validate
{
    protected $rules = [
        'topic' => 'required',
        'broker' => 'required',
        'isAvroSchema' => 'boolean',
        'offset-reset' => 'required',
        'offset' => 'required|integer',
        'partition' => 'required|integer',
        'handle' => 'required|string',
        'timeout' => 'required|integer',
        'connections' => 'required|string',
        'schemaUri' => 'string',
        'auth' => 'array',
    ];

    public function setOptionConfig($options, $arguments): void
    {
        $topicConfig = $this->getTopicConfig($arguments['topic']);
        $consumerConfig = $this->getConsumerConfig($arguments['consumer-group'], $topicConfig);
        $brokerConfig = $this->getBrokerConfig($topicConfig['broker']);
        $config = array_merge($topicConfig, $brokerConfig, $consumerConfig, $options, $arguments);

        $this->validateConfig($config);
        $this->setConfigRuntime($config);
    }

    private function getTopicConfig($topicId): array
    {
        $topicConfig = config('kafka.topics.' . $topicId);
        if (!$topicConfig) {
            throw new ConfigurationException("Topic '{$topicId}' not found");
        }

        return $topicConfig;
    }

    private function getConsumerConfig(string $consumerGroupId, array $topicConfig): array
    {
        if (!$consumerGroupId && 1 === count($topicConfig['consumer-groups'])) {
            $consumerGroupId = current(array_keys($topicConfig['consumer-groups']));
        }

        $consumerGroupId = $consumerGroupId ?? 'default';
        $consumerConfig = $topicConfig['consumer-groups'][$consumerGroupId] ?? null;

        if (!$consumerConfig) {
            throw new ConfigurationException("Consumer group '{$consumerGroupId}' not found");
        }

        return $consumerConfig;
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
