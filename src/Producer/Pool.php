<?php
namespace Metamorphosis\Producer;

use Metamorphosis\Facades\ConfigManager;
use RdKafka\Producer;
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
    private $maxPoolRecords;

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
        $this->maxPoolRecords = ConfigManager::get('max_pool_records');
        $this->requiredAcknowledgment = ConfigManager::get('required_acknowledgment');
        $this->maxFlushAttempts = ConfigManager::get('flush_attempts');
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

        if (0 === ($this->processMessageCount % $this->maxPoolRecords)) {
            $this->flushMessage();
        }
    }

    public function flushMessage(): void
    {
        if (!$this->requiredAcknowledgment) {
            return;
        }

        for ($flushAttempts = 0; $flushAttempts < $this->maxFlushAttempts; $flushAttempts++) {
            if (0 === $this->producer->pool($this->timeout)) {
                return;
            }
        }

        throw new RuntimeException('Unable to flush, messages might be lost!');
    }
}
