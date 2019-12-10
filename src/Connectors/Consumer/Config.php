<?php
namespace Metamorphosis\Config;

use Illuminate\Support\Facades\Validator;
use Metamorphosis\Exceptions\ConfigurationException;

class Validate
{
    protected $rules = [
        'topic' => 'required',
        'broker' => 'required',
        'offset-reset' => 'required', // latest, earliest, none
        'offset' => 'required_with:partition|integer',
        'partition' => 'required_with:offset|integer',
        'handler' => 'required|string',
        'timeout' => 'required|integer',
        'consumerGroupId' => 'required|string',
        'connections' => 'required|string',
        'schemaUri' => 'string',
        'auth' => 'array',
    ];

    public function setOptionConfig(array $options, array $arguments): void
    {
        $topicConfig = $this->getTopicConfig($arguments['topic']);
        $consumerConfig = $this->getConsumerConfig($topicConfig, $arguments['consumer-group']);
        $brokerConfig = $this->getBrokerConfig($topicConfig['broker']);
        $config = array_merge($topicConfig, $brokerConfig, $consumerConfig, array_filter($options), array_filter($arguments));

        $this->validateConfig($config);
        $this->setConfigRuntime($config);
    }

    private function getTopicConfig(string $topicId): array
    {
        $topicConfig = config('kafka.topics.' . $topicId);
        if (!$topicConfig) {
            throw new ConfigurationException("Topic '{$topicId}' not found");
        }

        return $topicConfig;
    }

    private function getConsumerConfig(array $topicConfig, string $consumerGroupId = null): array
    {
        if (!$consumerGroupId && 1 === count($topicConfig['consumer-groups'])) {
            $consumerGroupId = current(array_keys($topicConfig['consumer-groups']));
        }

        $consumerGroupId = $consumerGroupId ?? 'default';
        $consumerConfig = $topicConfig['consumer-groups'][$consumerGroupId] ?? null;
        $consumerConfig['consumerGroupId'] = $consumerGroupId;

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