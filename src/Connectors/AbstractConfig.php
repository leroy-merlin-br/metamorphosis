<?php

namespace Metamorphosis\Connectors;

use Illuminate\Support\Facades\Validator;
use Metamorphosis\Exceptions\ConfigurationException;

abstract class AbstractConfig
{
    /**
     * @var string[]
     */
    protected array $rules;

    /**
     * @psalm-suppress InvalidReturnStatement
     * @throws ConfigurationException
     */
    protected function getBrokerConfig(string $servicesFile): array
    {
        if (!$brokerConfig = config($servicesFile . '.broker')) {
            throw new ConfigurationException(
                "Broker configuration not found on '{$servicesFile}'"
            );
        }

        return $brokerConfig;
    }

    protected function getSchemaConfig(string $servicesFile): array
    {
        return config($servicesFile . '.avro_schema', []);
    }

    protected function validate(array $config): void
    {
        $validator = Validator::make($config, $this->rules);

        if (!$validator->errors()->isEmpty()) {
            throw new ConfigurationException($validator->errors()->toJson());
        }
    }
}
