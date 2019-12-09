<?php
namespace Metamorphosis\Config;

use Illuminate\Support\Facades\Validator;
use Metamorphosis\Exceptions\ConfigurationException;
use RuntimeException;

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
        if (!is_null($options['offset']) && is_null($options['partition'])) {
            throw new RuntimeException('Not enough options ("partition" is required when "offset" is supplied).');
        }

        $topicConfig = $this->getTopicConfig($arguments['topic']);
        $consumerConfig = $this->getConsumerConfig($arguments['consumer-group'], $topicConfig);
        $brokerConfig = $this->getBrokerConfig($topicConfig['broker']);

        $config = array_merge($topicConfig, $brokerConfig, $consumerConfig, $options, $arguments);

        $validator = Validator::make($config, $this->rules);
        if (!$validator->errors()->isEmpty()) {
            throw new ConfigurationException($validator->errors()->toJson());
        }

        config(['kafka.runtime' => $config]);
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
}
