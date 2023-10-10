<?php

namespace Metamorphosis\Consumers;

use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use RdKafka\ConsumerTopic;
use RdKafka\Message;

class LowLevel implements ConsumerInterface
{
    protected ConsumerTopic $consumer;

    private int $partition;

    private ?int $timeout;

    public function __construct(ConsumerTopic $consumer, ConsumerConfigOptions $consumerConfigOptions)
    {
        $this->consumer = $consumer;

        $this->partition = $consumerConfigOptions->getPartition();
        $this->timeout = $consumerConfigOptions->getTimeout();
    }

    public function consume(): ?Message
    {
        return $this->consumer->consume($this->partition, $this->timeout);
    }

    /**
     *  When running low level consumer, we dont need
     * to commit the messages as they've already been committed.
     */
    public function canCommit(): bool
    {
        return false;
    }
}
