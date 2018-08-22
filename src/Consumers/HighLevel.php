<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Record;
use RdKafka\KafkaConsumer;

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

    /**
     * @param int $timeout
     *
     * @return Record
     */
    public function consume(int $timeout): Record
    {
        return $this->consumer->consume($timeout);
    }
}
