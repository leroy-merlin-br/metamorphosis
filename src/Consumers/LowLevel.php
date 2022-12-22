<?php

namespace Metamorphosis\Consumers;

use Metamorphosis\AbstractConfigManager;
use RdKafka\ConsumerTopic;
use RdKafka\Message;

class LowLevel implements ConsumerInterface
{
    protected ConsumerTopic $consumer;

    private int $partition;

    private ?int $timeout;

    public function __construct(ConsumerTopic $consumer, AbstractConfigManager $configManager)
    {
        $this->consumer = $consumer;

        $this->partition = $configManager->get('partition');
        $this->timeout = $configManager->get('timeout');
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
