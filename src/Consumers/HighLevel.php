<?php
namespace Metamorphosis\Consumers;

use RdKafka\KafkaConsumer;
use RdKafka\Message;

class HighLevel implements ConsumerInterface
{
    /**
     * @var KafkaConsumer
     */
    protected $consumer;

    /**
     * @var int
     */
    private $timeout;

    public function __construct(KafkaConsumer $consumer, int $timeout)
    {
        $this->consumer = $consumer;

        $this->timeout = $timeout;
    }

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

    public function canCommit(): bool
    {
        return true;
    }
}
