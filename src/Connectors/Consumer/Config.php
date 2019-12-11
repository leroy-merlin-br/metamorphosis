<?php
namespace Metamorphosis\Connectors\Consumer;

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
        'offset_reset' => 'required', // latest, earliest, none
        'offset' => 'required_with:partition|integer',
        'partition' => 'integer',
        'handler' => 'required|string',
        'timeout' => 'required|integer',
        'consumer-group' => 'required|string',
        'connections' => 'required|string',
        'schemaUri' => 'string',
        'use_avro_schema' => 'boolean',
        'auth' => 'array',
        'middlewares' => 'array',
    ];

    public function setOptionConfig(array $options, array $arguments): void
    {
        $topicConfig = $this->getTopicConfig($arguments['topic']);
        $consumerConfig = $this->getConsumerConfig($topicConfig, $arguments['consumer-group']);
        $brokerConfig = $this->getBrokerConfig($topicConfig['broker']);
        $config = array_merge(
            $topicConfig,
            $brokerConfig,
            $consumerConfig,
            $this->filterValues($options),
            $this->filterValues($arguments)
        );

        $this->validateConfig($config);
        $this->setConfigRuntime($config);
    }

    private function getTopicConfig(string $topicId): array
    {
        $topicConfig = config('kafka.topics.'.$topicId);
        if (!$topicConfig) {
            throw new ConfigurationException("Topic '{$topicId}' not found");
        }

        $topicConfig['middlewares'] = $this->getMiddlewares($topicConfig);

        return $topicConfig;
    }

    private function getConsumerConfig(array $topicConfig, string $consumerGroupId = null): array
    {
        if (!$consumerGroupId && 1 === count($topicConfig['consumer_groups'])) {
            $consumerGroupId = current(array_keys($topicConfig['consumer_groups']));
        }

        $consumerGroupId = $consumerGroupId ?? 'default';
        $consumerConfig = $topicConfig['consumer_groups'][$consumerGroupId] ?? null;
        $consumerConfig['consumer-group'] = $consumerGroupId;

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

    private function getMiddlewares(array $topicConfig): array
    {
        return array_merge(
            config('kafka.middlewares.consumer', []),
            $topicConfig['middlewares'] ?? []
        );
    }

    private function filterValues(array $options = []): array
    {
        return array_filter($options, function ($value) {
            return !is_null($value);
        });
    }
}
