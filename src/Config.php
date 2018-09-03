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
     * @var int
     */
    protected $consumerPartition;

    /**
     * @var string
     */
    protected $consumerOffsetReset;

    /**
     * @var int
     */
    protected $consumerOffset;

    /**
     * @var Handler
     */
    protected $consumerHandler;

    /**
     * @var iterable
     */
    protected $middlewares = [];

    public function __construct(
        string $topic,
        string $consumerGroupId = null,
        int $partition = null,
        int $offset = null
    ) {
        $topicConfig = $this->getTopicConfig($topic);
        $this->setGlobalMiddlewares();
        $this->setTopic($topicConfig);
        $this->setConsumerGroup($topicConfig, $consumerGroupId, $partition, $offset);
        $this->setProducer($topicConfig);
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

    public function getConsumerOffsetReset(): string
    {
        return $this->consumerOffsetReset;
    }

    public function getConsumerOffset(): int
    {
        return $this->consumerOffset;
    }

    public function getConsumerHandler(): Handler
    {
        return $this->consumerHandler;
    }

    public function getMiddlewares(): iterable
    {
        return $this->middlewares;
    }

    public function getConsumerPartition(): ?int
    {
        return $this->consumerPartition;
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
        int $partition = null,
        int $offset = null
    ): void {
        if (!$consumerGroupId && count($topicConfig['consumer-groups']) === 1) {
            $consumerGroupId = current(array_keys($topicConfig['consumer-groups']));
        }

        $consumerGroupId = $consumerGroupId ?? 'default';

        $consumerConfig = $topicConfig['consumer-groups'][$consumerGroupId] ?? null;

        if (!$consumerConfig) {
            throw new ConfigurationException("Consumer group '{$consumerGroupId}' not found");
        }

        $this->consumerGroupId = $consumerGroupId;
        $this->consumerPartition = !is_null($partition) ? $partition : ($consumerConfig['partition'] ?? null);
        $this->consumerOffsetReset = $consumerConfig['offset-reset'] ?? 'largest';
        $this->consumerOffset = !is_null($offset) ? $offset : $consumerConfig['offset'];
        $this->consumerHandler = app($consumerConfig['consumer']);

        $this->setMiddlewares($consumerConfig['middlewares'] ?? []);
    }

    private function setProducer(array $topicConfig): void
    {
        $producerConfig = $topicConfig['producer'] ?? null;

        if (!$producerConfig) {
            return;
        }

        $this->setMiddlewares($producerConfig['middlewares'] ?? []);
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
