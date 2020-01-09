<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Connectors\AbstractConfig;
use Metamorphosis\Exceptions\ConfigurationException;

class Config extends AbstractConfig
{
    /**
     * @var array
     */
    protected $rules = [
        'topic' => 'required',
        'broker' => 'required',
        'connections' => 'required|string',
        'timeout' => 'int',
        'is_async' => 'bool',
        'required_acknowledgment' => 'bool',
        'max_poll_records' => 'int',
        'flush_attempts' => 'int',
        'auth' => 'array',
        'middlewares' => 'array',
    ];

    public function setOption(string $topicId): void
    {
        $topicConfig = $this->getTopicConfig($topicId);
        $topicConfig['middlewares'] = array_merge(
            config('kafka.middlewares.producer', []),
            $topicConfig['middlewares'] ?? []
        );
        $brokerConfig = $this->getBrokerConfig($topicConfig['broker']);
        $config = array_merge($topicConfig, $brokerConfig);

        $this->validate($config);
        $this->setConfigRuntime($config);
    }

    private function getTopicConfig(string $topicId): array
    {
        $topicConfig = config('kafka.topics.'.$topicId);
        if (!$topicConfig) {
            throw new ConfigurationException("Topic '{$topicId}' not found");
        }
        $topicConfig['topic'] = $topicId;

        return $topicConfig;
    }
}
