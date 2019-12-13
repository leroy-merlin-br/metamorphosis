<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Manager;
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

    public function __construct(KafkaConsumer $consumer)
    {
        $this->consumer = $consumer;

        $this->timeout = Manager::get('timeout');
    }

    public function consume(): Message
    {
        return $this->consumer->consume($this->timeout);
    }
}
