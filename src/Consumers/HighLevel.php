<?php

namespace Metamorphosis\Consumers;

use RdKafka\KafkaConsumer;
use RdKafka\Message;
use Override;

class HighLevel implements ConsumerInterface
{
    protected KafkaConsumer $consumer;

    private int $timeout;

    public function __construct(KafkaConsumer $consumer, int $timeout)
    {
        $this->consumer = $consumer;

        $this->timeout = $timeout;
    }

    #[Override]
    public function consume(): ?Message
    {
        return $this->consumer->consume($this->timeout);
    }

    public function commit(): void
    {
        $this->consumer->commit();
    }

    public function commitAsync(): void
    {
        $this->consumer->commitAsync();
    }

    #[Override]
    public function canCommit(): bool
    {
        return true;
    }
}
