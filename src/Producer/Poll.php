<?php

namespace Metamorphosis\Producer;

use Metamorphosis\AbstractConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ConfigOptions;
use RdKafka\Producer;
use RuntimeException;

class Poll
{
    const NON_BLOCKING_POLL = 0;

    private int $processedMessagesCount = 0;

    private ?bool $isAsync;

    private ?int $maxPollRecords;

    private ?bool $requiredAcknowledgment;

    private ?int $maxFlushAttempts;

    private ?int $timeout;

    private Producer $producer;

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
        $this->producer->poll(self::NON_BLOCKING_POLL);
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
            if (
                RD_KAFKA_RESP_ERR_NO_ERROR === $this->producer->flush(
                    $this->timeout
                )
            ) {
                return;
            }

            sleep($this->timeout / 1000);
        }

        throw new RuntimeException('Unable to flush, messages might be lost!');
    }
}
