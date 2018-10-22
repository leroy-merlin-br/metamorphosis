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

    public function __construct(KafkaConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    public function consume(int $timeout): Message
    {
        return $this->consumer->consume($timeout);
    }
}
