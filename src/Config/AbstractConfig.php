<?php declare(strict_types=1);
namespace Metamorphosis\Config;

use Metamorphosis\Broker;
use Metamorphosis\Exceptions\ConfigurationException;

/**
 * Maps configuration from config file and provides access to them via methods.
 */
abstract class AbstractConfig
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
     * @var bool
     */
    protected $enableHighPerformance;

    /**
     * @var iterable
     */
    protected $middlewares = [];

    public function __construct(string $topic, string $broker = null)
    {
        $topicConfig = $this->getTopicConfig($topic);
        $this->setGlobalMiddlewares();
        $this->setTopic($topicConfig);
        $this->setHighPerformanceConfig($topic);
        $this->setBroker($broker ?: $topicConfig['broker']);
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getBrokerConfig(): Broker
    {
        return $this->broker;
    }

    public function getMiddlewares(): iterable
    {
        return $this->middlewares;
    }

    public function isHighPerformanceEnabled(): bool
    {
        return $this->enableHighPerformance;
    }

    protected function setHighPerformanceConfig(string $topic): void
    {
        $highPerformance = (bool) config("kafka.topics.{$topic}.high-performance", true);
        $this->enableHighPerformance = $highPerformance;
    }

    protected function getTopicConfig(string $topic): array
    {
        $config = config("kafka.topics.{$topic}");

        if (!$config) {
            throw new ConfigurationException("Topic '{$topic}' not found");
        }

        return $config;
    }

    protected function setBroker(string $brokerKey): void
    {
        $brokerConfig = config("kafka.brokers.{$brokerKey}");

        if (!$brokerConfig) {
            throw new ConfigurationException("Broker '{$brokerKey}' configuration not found");
        }

        $this->broker = new Broker($brokerConfig['connections'], $brokerConfig['auth'] ?? null);
    }

    protected function setTopic(array $topicConfig): void
    {
        $this->topic = $topicConfig['topic'];

        $this->setMiddlewares($topicConfig['middlewares'] ?? []);
    }

    protected function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = array_unique(array_merge($this->middlewares, $middlewares));
    }

    protected function setGlobalMiddlewares(): void
    {
        $this->setMiddlewares(config('kafka.middlewares.global', []));
    }
}
