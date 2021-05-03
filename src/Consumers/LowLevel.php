<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Facades\ConfigManager;
use RdKafka\ConsumerTopic;
use RdKafka\Message;

class LowLevel implements ConsumerInterface
{
    /**
     * @var ConsumerTopic
     */
    protected $consumer;

    /**
     * @var int
     */
    private $partition;

    /**
     * @var int
     */
    private $timeout;

    public function __construct(ConsumerTopic $consumer)
    {
        $this->consumer = $consumer;

        $this->partition = ConfigManager::get('partition');
        $this->timeout = ConfigManager::get('timeout');
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
