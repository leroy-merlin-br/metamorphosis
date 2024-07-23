<?php

namespace Metamorphosis\Connectors\Consumer;

use InvalidArgumentException;
use Metamorphosis\Connectors\AbstractConfig;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer;
use Metamorphosis\TopicHandler\ConfigOptions\Factories\ConsumerFactory;

/**
 * This class is responsible for handling all configuration made on the
 * kafka config file as well as the override config passed as argument
 * on kafka:consume command.
 *
 * It will generate a `runtime` configuration that will be used in all
 * classes. The config will be on Manager singleton class.
 */
class Config extends AbstractConfig
{
    /**
     * @var string[]
     */
    protected array $rules = [
        'topic' => 'required',
        'broker' => 'required',
        'offset_reset' => 'required', // latest, earliest, none
        'offset' => 'required_with:partition|integer',
        'partition' => 'integer',
        'handler' => 'required|string',
        'timeout' => 'required|integer',
        'consumer_group' => 'required|string',
        'connections' => 'required|string',
        'url' => 'string',
        'ssl_verify' => 'boolean',
        'auth' => 'array',
        'request_options' => 'array',
        'auto_commit' => 'boolean',
        'commit_async' => 'boolean',
        'middlewares' => 'array',
    ];

    public function makeWithConfigOptions(string $handlerClass): ?Consumer
    {
        $handler = app($handlerClass);
        $configOptions = $handler->getConfigOptions();
        if (is_null($configOptions)) {
            throw new InvalidArgumentException('Handler class cannot be null');
        }

        return $configOptions;
    }

    public function make(array $options, array $arguments): Consumer
    {
        $configName = $options['config_name'] ?? 'kafka';
        $service = $options['service_name'] ?? 'service';

        $topicConfig = $this->getTopicConfig($configName, $arguments['topic']);
        $brokerConfig = $this->getBrokerConfig($service);
        $schemaConfig = $this->getSchemaConfig($service);

        if (isset($topicConfig['consumer'])) {
            if (isset($options['partition'])) {
                $topicConfig['consumer']['partition'] = $options['partition'];
            }

            if (isset($options['offset'])) {
                $topicConfig['consumer']['offset'] = $options['offset'];
            }

            if (isset($options['timeout'])) {
                $topicConfig['consumer']['timeout'] = $options['timeout'];
            }
        }

        return ConsumerFactory::make(
            $brokerConfig,
            $topicConfig,
            $schemaConfig
        );
    }

    /**
     * @psalm-suppress InvalidReturnStatement
     */
    private function getTopicConfig(string $configName, string $topicId): array
    {
        $topicConfig = config($configName . '.topics.' . $topicId);
        if (!$topicConfig) {
            throw new ConfigurationException("Topic '{$topicId}' not found");
        }

        $topicConfig['middlewares'] = config(
            'kafka.middlewares.consumer',
            []
        );

        return $topicConfig;
    }
}
