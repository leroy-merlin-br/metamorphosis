<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\ConfigManager;
use Metamorphosis\Connectors\AbstractConfig;
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

    public function make(array $options, array $arguments): ConfigManager
    {
        $topicConfig = $this->getTopicConfig($arguments['topic']);
        $consumerConfig = $this->getConsumerConfig($topicConfig, $arguments['consumer_group']);
        $brokerConfig = $this->getBrokerConfig($topicConfig['broker']);
        $schemaConfig = $this->getSchemaConfig($arguments['topic']);
        $config = array_merge(
            $topicConfig,
            $brokerConfig,
            $consumerConfig,
            $this->filterValues($options),
            $this->filterValues($arguments),
            $schemaConfig
        );

        $this->validate($config);
        $configManager = app(ConfigManager::class);
        $configManager->set($config);

        return $configManager;
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
        if (!$consumerGroupId && 1 === count($topicConfig['consumer']['consumer_groups'])) {
            $consumerGroupId = current(array_keys($topicConfig['consumer']['consumer_groups']));
        }

        $consumerGroupId = $consumerGroupId ?? 'default';
        $consumerConfig = $topicConfig['consumer']['consumer_groups'][$consumerGroupId] ?? null;
        $consumerConfig['consumer_group'] = $consumerGroupId;

        if (!$consumerConfig) {
            throw new ConfigurationException("Consumer group '{$consumerGroupId}' not found");
        }

        return $consumerConfig;
    }

    private function getMiddlewares(array $topicConfig): array
    {
        return array_merge(
            config('kafka.middlewares.consumer', []),
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
