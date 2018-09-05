<?php declare(strict_types=1);
namespace Metamorphosis\Config;

use Metamorphosis\Broker;

/**
 * Maps configuration from config file and provides access to them via methods.
 */
class Producer extends Config
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
     * @var iterable
     */
    protected $middlewares = [];

    public function __construct(string $topic)
    {
        parent::__construct($topic);

        $this->setProducer($this->getTopicConfig($topic));
    }

    private function setProducer(array $topicConfig): void
    {
        $producerConfig = $topicConfig['producer'] ?? null;

        if (!$producerConfig) {
            return;
        }

        $this->setMiddlewares($producerConfig['middlewares'] ?? []);
    }

    protected function setGlobalMiddlewares(): void
    {
        parent::setGlobalMiddlewares();
        $this->setMiddlewares(config('kafka.middlewares.producer', []));
    }
}
