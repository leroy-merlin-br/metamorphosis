<?php declare(strict_types=1);
namespace Metamorphosis;

use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\Consumer\Handler;

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
    protected $consumerGroupOffsetReset;

    /**
     * @var int
     */
    protected $consumerGroupOffset;

    /**
     * @var Handler
     */
    protected $consumerGroupHandler;

    /**
     * @var iterable
     */
    protected $middlewares = [];

    public function __construct(
        string $topic,
        string $consumerGroup = null,
        string $offset = null,
        int $partition = null
    ) {
        $topicConfig = $this->getTopicConfig($topic);
        $this->setGlobalMiddlewares();
        $this->setTopic($topicConfig);
        $this->setConsumerGroup($topicConfig, $consumerGroup, $offset);
        $this->setBroker($topicConfig);
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

    public function getConsumerGroupOffsetReset(): string
    {
        return $this->consumerGroupOffsetReset;
    }

    public function getConsumerGroupOffset(): int
    {
        return $this->consumerGroupOffset;
    }

    public function getConsumerGroupHandler(): Handler
    {
        return $this->consumerGroupHandler;
    }

    public function getMiddlewares(): iterable
    {
        return $this->middlewares;
    }

    public function getPartition(): ?int
    {
        return $this->partition;
    }

    private function getTopicConfig(string $topic): array
    {
        $config = config("kafka.topics.{$topic}");

        if (!$config) {
            throw new ConfigurationException("Topic '{$topic}' not found");
        }

        return $config;
    }

    private function setConsumerGroup(
        array $topicConfig,
        string $consumerGroupId = null,
        string $offset = null
    ): void {
        if (!$consumerGroupId && count($topicConfig['consumer-groups']) === 1) {
            $consumerGroupId = current(array_keys($topicConfig['consumer-groups']));
        }

        $consumerGroupId = $consumerGroupId ?? 'default';

        $consumerGroupConfig = $topicConfig['consumer-groups'][$consumerGroupId] ?? null;

        if (!$consumerGroupConfig) {
            throw new ConfigurationException("Consumer group '{$consumerGroupId}' not found");
        }

        $this->consumerGroupId = $consumerGroupId;
        $this->consumerGroupOffsetReset = $consumerGroupConfig['offset-reset'];
        $this->consumerGroupOffset = !is_null($offset) ? $offset : $consumerGroupConfig['offset'];
        $this->consumerGroupHandler = app($consumerGroupConfig['consumer']);

        $this->setMiddlewares($consumerGroupConfig['middlewares'] ?? []);
    }

    private function setBroker(array $topicConfig): void
    {
        $brokerConfig = config("kafka.brokers.{$topicConfig['broker']}");

        if (!$brokerConfig) {
            throw new ConfigurationException("Broker '{$topicConfig['broker']}' configuration not found");
        }

        $this->broker = new Broker($brokerConfig['connections'], $brokerConfig['auth'] ?? null);
    }

    private function setTopic(array $topicConfig): void
    {
        $this->topic = $topicConfig['topic'];

        $this->setMiddlewares($topicConfig['middlewares'] ?? []);
    }

    private function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = array_unique(array_merge($this->middlewares, $middlewares));
    }

    private function setGlobalMiddlewares(): void
    {
        $this->setMiddlewares(config('kafka.middlewares.consumer', []));
    }
}
