<?php

namespace Metamorphosis\Connectors;

use Illuminate\Support\Facades\Validator;
use Metamorphosis\Exceptions\ConfigurationException;

abstract class AbstractConfig
{
    protected function getBrokerConfig(string $configName, string $brokerId): array
    {
        if (!$brokerConfig = config($configName . ".brokers.{$brokerId}")) {
            throw new ConfigurationException(
                "Broker '{$brokerId}' configuration not found"
            );
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

    protected function getSchemaConfig(string $configName, string $topicId): array
    {
        return config($configName . '.avro_schemas.' . $topicId, []);
    }
}
