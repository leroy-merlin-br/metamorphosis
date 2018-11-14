<?php declare(strict_types=1);
namespace Metamorphosis\Config;

/**
 * Maps configuration from config file and provides access to them via methods.
 */
class Producer extends AbstractConfig
{
    /**
     * @var int
     */
    protected $timeoutResponses = 1;

    public function __construct(string $topic)
    {
        parent::__construct($topic);

        $this->setProducer($this->getTopicConfig($topic));
    }

    private function setProducer(array $topicConfig): void
    {
        $producerConfig = $topicConfig['producer'] ?? null;

        $this->setMiddlewares($producerConfig['middlewares'] ?? []);

        $this->setTimeoutResponse($producerConfig['timeout-responses'] ?? $this->timeoutResponses);
    }

    public function getTimeoutResponse(): int
    {
        return $this->timeoutResponses;
    }

    protected function setTimeoutResponse(int $timeout): void
    {
        $this->timeoutResponses = $timeout;
    }

    protected function setGlobalMiddlewares(): void
    {
        parent::setGlobalMiddlewares();
        $this->setMiddlewares(config('kafka.middlewares.producer', []));
    }
}
