<?php
namespace Metamorphosis\Connectors;

use Illuminate\Support\Facades\Validator;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Facades\ConfigManager;

abstract class AbstractConfig
{
    protected function getBrokerConfig(string $brokerId): array
    {
        if (!$brokerConfig = config("kafka.brokers.{$brokerId}")) {
            throw new ConfigurationException("Broker '{$brokerId}' configuration not found");
        }

        return $brokerConfig;
    }

    protected function validate(array $config): void
    {
        $validator = Validator::make($config, $this->rules);

        if (!$validator->errors()->isEmpty()) {
            throw new ConfigurationException($validator->errors()->toJson());
        }
    }

    protected function setConfigRuntime(array $config): void
    {
        ConfigManager::set($config);
    }

    protected function getSchemaConfig(string $topicId): array
    {
        return config('kafka.avro_schemas.'.$topicId, []);
    }
}
