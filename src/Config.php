<?php declare(strict_types=1);
namespace Metamorphosis;

use Metamorphosis\Contracts\ConsumerTopicHandler;
use Metamorphosis\Exceptions\ConfigurationException;

/**
 * Maps configuration from config file and provides access to them via methods.
 */
class Config
{
    /**
     * @var string
     */
    protected $topic;

    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @var string
     */
    protected $consumerGroupId;

    /**
     * @var string
     */
    protected $consumerGroupOffset;

    /**
     * @var ConsumerTopicHandler
     */
    protected $consumerGroupHandler;

    public function __construct(string $topic, string $consumerGroup = null)
    {
        $topicConfig = $this->getTopicConfig($topic);
        $this->setConsumerGroup($topicConfig, $consumerGroup);
        $this->setBroker($topicConfig);
        $this->setTopic($topicConfig);
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getBrokerConfig(): Broker
    {
        return $this->broker;
    }

    public function getConsumerGroupId(): string
    {
        return $this->consumerGroupId;
    }

    public function getConsumerGroupOffset(): string
    {
        return $this->consumerGroupOffset;
    }

    public function getConsumerGroupHandler(): ConsumerTopicHandler
    {
        return $this->consumerGroupHandler;
    }

    private function getTopicConfig(string $topic): array
    {
        $config = config("kafka.topics.{$topic}");

        if (!$config) {
            throw new ConfigurationException("Topic '{$topic}' not found");
        }

        return $config;
    }

    private function setConsumerGroup(array $topicConfig, string $consumerGroupId = null): void
    {
        $consumerGroupId = $consumerGroupId ?? 'default';

        $consumerGroupConfig = $topicConfig['consumer-groups'][$consumerGroupId] ?? null;

        if (!$consumerGroupConfig) {
            throw new ConfigurationException("Consumer group '{$consumerGroupId}' not found");
        }

        $this->consumerGroupId = $consumerGroupId;
        $this->consumerGroupOffset = $consumerGroupConfig['offset'];
        $this->consumerGroupHandler = app($consumerGroupConfig['consumer']);
    }

    private function setBroker(array $topicConfig): void
    {
        $brokerConfig = config("kafka.brokers.{$topicConfig['broker']}");

        if (!$brokerConfig) {
            throw new ConfigurationException("Broker '{$topicConfig['broker']}' configuration not found");
        }

        $this->broker = new Broker();
        $this->broker->setConnection($brokerConfig['connection']);
        $this->broker->setAuthentication($brokerConfig['auth']);
    }

    private function setTopic(array $topicConfig): void
    {
        $this->topic = $topicConfig['topic'];
    }
}
