<?php

namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Connectors\AbstractConfig;
use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\Exceptions\ConfigurationException;

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
     * @var array<string, string>
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

    public function makeWithConfigOptions(string $handlerClass, ?int $times = null): AbstractConfigManager
    {
        $configManager = app(ConsumerConfigManager::class);
        $configManager->set(['handler' => $handlerClass], ['times' => $times]);

        return $configManager;
    }

    public function make(array $options, array $arguments): AbstractConfigManager
    {
        $configName = $options['config_name'] ?? 'kafka';
        $topicConfig = $this->getTopicConfig($configName, $arguments['topic']);
        $consumerConfig = $this->getConsumerConfig(
            $topicConfig,
            $arguments['consumer_group']
        );
        $brokerConfig = $this->getBrokerConfig(
            $configName,
            $topicConfig['broker']
        );
        $schemaConfig = $this->getSchemaConfig(
            $configName,
            $arguments['topic']
        );
        $override = array_merge(
            $this->filterValues($options),
            $this->filterValues($arguments)
        );
        $config = array_merge(
            $topicConfig,
            $brokerConfig,
            $consumerConfig,
            $schemaConfig
        );

        $this->validate(array_merge($config, $override));
        $configManager = app(ConsumerConfigManager::class);
        $configManager->set($config, $override);

        return $configManager;
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

        $topicConfig['middlewares'] = $this->getMiddlewares(
            $configName,
            $topicConfig
        );

        return $topicConfig;
    }

    private function getConsumerConfig(array $topicConfig, ?string $consumerGroupId = null): array
    {
        if (
            !$consumerGroupId && 1 === count(
                $topicConfig['consumer']['consumer_groups']
            )
        ) {
            $consumerGroupId = current(
                array_keys($topicConfig['consumer']['consumer_groups'])
            );
        }

        $consumerGroupId = $consumerGroupId ?? 'default';
        $consumerConfig = $topicConfig['consumer']['consumer_groups'][$consumerGroupId] ?? null;
        $consumerConfig['consumer_group'] = $consumerGroupId;

        if (!$consumerConfig) {
            throw new ConfigurationException(
                "Consumer group '{$consumerGroupId}' not found"
            );
        }

        return $consumerConfig;
    }

    private function getMiddlewares(string $configName, array $topicConfig): array
    {
        return array_merge(
            config($configName . '.middlewares.consumer', []),
            $topicConfig['consumer']['middlewares'] ?? []
        );
    }

    /**
     * Sometimes that user may pass `--partition=0` as argument.
     * So if we just use array_filter here, this option will
     * be removed.
     *
     * This code makes sure that only null values will be removed.
     */
    private function filterValues(array $options = []): array
    {
        return array_filter($options, function ($value) {
            return !is_null($value);
        });
    }
}
