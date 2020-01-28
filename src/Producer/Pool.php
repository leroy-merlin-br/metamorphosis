<?php
namespace Metamorphosis\Producer;

use Metamorphosis\Facades\ConfigManager;
use RuntimeException;

class Pool
{
    /**
     * @var int
     */
    private $processMessageCount = 0;

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
    private $flushAttempts;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var \RdKafka\Producer
     */
    private $producer;

    public function __construct(\RdKafka\Producer $producer)
    {
        $this->isAsync = ConfigManager::get('is_async');
        $this->maxPollRecords = ConfigManager::get('max_poll_records');
        $this->requiredAcknowledgment = ConfigManager::get('required_acknowledgment');
        $this->flushAttempts = ConfigManager::get('flush_attempts');
        $this->timeout = ConfigManager::get('timeout');

        $this->producer = $producer;
    }

    public function handleResponse(): void
    {
        $this->processMessageCount++;

        if (!$this->isAsync) {
            $this->flushMessage();

            return;
        }

        if (0 === ($this->processMessageCount % $this->maxPollRecords)) {
            $this->flushMessage();
        }
    }

    public function flushMessage(): void
    {
        if (!$this->requiredAcknowledgment) {
            return;
        }

        for ($flushAttempts = 0; $flushAttempts < $this->flushAttempts; $flushAttempts++) {
            if (0 === $this->producer->poll($this->timeout)) {
                return;
            }
        }

        throw new RuntimeException('Unable to flush, messages might be lost!');
    }
}
