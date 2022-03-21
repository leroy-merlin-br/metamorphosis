<?php

namespace Metamorphosis\Connectors\Consumer;

use InvalidArgumentException;
use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Connectors\AbstractConfig;
use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer;
use Metamorphosis\TopicHandler\ConfigOptions\Factories\ConsumerFactory;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

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

    /**
     * @param string $handlerClass
     * @param int|null $times
     * @return Consumer
     */
    public function makeWithConfigOptions(string $handlerClass, ?int $times = null): ?Consumer
    {
        /** @var  AbstractHandler */
        $handler = app($handlerClass);
        $configOptions = $handler->getConfigOptions();
        if(is_null($configOptions)){
            throw new InvalidArgumentException('Handler class cannot be null');
        }

        return $configOptions;
    }

    public function make(array $options, array $arguments): Consumer
    {
        $configName = $options['config_name'] ?? 'kafka';
        $topicConfig = $this->getTopicConfig($configName, $arguments['topic']);
        $consumerGroupId = $this->getConsumerGroup($topicConfig, $arguments['consumer_group']);
        $consumerConfig = $this->getConsumerConfig($topicConfig, $arguments, $consumerGroupId);
        $brokerConfig = $this->getBrokerConfig($configName, $topicConfig['broker']);
        $schemaConfig = $this->getSchemaConfig($configName, $arguments['topic']);
        $override = array_merge($this->filterValues($options), $this->filterValues($arguments));
        $config = array_merge(
            $topicConfig,
            $brokerConfig,
            $consumerConfig,
            $schemaConfig
        );

        $this->validate(array_merge($config, $override));

        $topicConfig['consumer']['consumer_groups'][$consumerConfig['consumer_group']]['partition'] = $consumerConfig['partition'];
        $topicConfig['consumer_group'] = $consumerGroupId;

        return ConsumerFactory::make($brokerConfig, $topicConfig, $schemaConfig);
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

    private function getConsumerConfig(array $topicConfig, array $arguments, string $consumerGroupId): array
    {
        $consumerConfig = $topicConfig['consumer']['consumer_groups'][$consumerGroupId] ?? null;
        if (!$consumerConfig) {
            throw new ConfigurationException("Consumer group '{$consumerGroupId}' not found");
        }

        $consumerConfig['consumer_group'] = $consumerGroupId;

        if(isset($arguments['partition'])){
            $consumerConfig['partition'] = $arguments['partition'];
        }

        return $consumerConfig;
    }

    private function getConsumerGroup(array $topicConfig, ?string $consumerGroupId): string
    {
        if (!$consumerGroupId && 1 === count($topicConfig['consumer']['consumer_groups'])) {
            $consumerGroupId = current(array_keys($topicConfig['consumer']['consumer_groups']));
        }

        return $consumerGroupId ?? 'default';
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
