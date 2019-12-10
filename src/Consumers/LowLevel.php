<?php
namespace Metamorphosis\Consumers;

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

        $this->partition = config('kafka.runtime.partition');
        $this->timeout = config('kafka.runtime.timeout');
    }

    public function consume(): Message
    {
        return $this->consumer->consume($this->partition, $this->timeout);
    }
}
