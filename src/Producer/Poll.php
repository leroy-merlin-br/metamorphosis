<?php
namespace Metamorphosis\Producer;

use Metamorphosis\TopicHandler\ConfigOptions\Producer as ConfigOptions;
use RdKafka\Producer;
use RuntimeException;

class Poll
{
    /**
     * @var int
     */
    private $processedMessagesCount = 0;

    /**
     * @var bool
     */
    private $isAsync;

    /**
     * @var int
     */
    private $maxPollRecords;

    /**
     * @var bool
     */
    private $requiredAcknowledgment;

    /**
     * @var int
     */
    private $maxFlushAttempts;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var \RdKafka\Producer
     */
    private $producer;

    public function __construct(Producer $producer, ConfigOptions $configOptions)
    {
        $this->isAsync = $configOptions->isAsync();
        $this->maxPollRecords = $configOptions->getMaxPollRecords();
        $this->requiredAcknowledgment = $configOptions->isRequiredAcknowledgment();
        $this->maxFlushAttempts = $configOptions->getFlushAttempts();
        $this->timeout = $configOptions->getTimeout();

        $this->producer = $producer;
    }

    public function handleResponse(): void
    {
        $this->processedMessagesCount++;

        if (!$this->isAsync) {
            $this->flushMessage();

            return;
        }

        if (0 === ($this->processedMessagesCount % $this->maxPollRecords)) {
            $this->flushMessage();
        }
    }

    public function flushMessage(): void
    {
        if (!$this->requiredAcknowledgment) {
            return;
        }

        for ($flushAttempts = 0; $flushAttempts < $this->maxFlushAttempts; $flushAttempts++) {
            if (0 === $this->producer->poll($this->timeout)) {
                return;
            }

            sleep($this->timeout / 1000);
        }

        throw new RuntimeException('Unable to flush, messages might be lost!');
    }
}
