<?php
namespace Metamorphosis\Producer;

use Metamorphosis\Facades\ConfigManager;
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

    public function __construct(Producer $producer)
    {
        $this->isAsync = ConfigManager::get('is_async');
        $this->maxPollRecords = ConfigManager::get('max_poll_records');
        $this->requiredAcknowledgment = ConfigManager::get('required_acknowledgment');
        $this->maxFlushAttempts = ConfigManager::get('flush_attempts');
        $this->timeout = ConfigManager::get('timeout');

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
        }

        throw new RuntimeException('Unable to flush, messages might be lost!');
    }
}
