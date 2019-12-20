<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Facades\Manager;
use Kafka\Consumer;

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

    public function __construct(Consumer $consumer)
    {
        $this->consumer = $consumer;

        $this->timeout = Manager::get('timeout');
    }

    public function consume(): Message
    {
        return $this->consumer->consume($this->timeout);
    }
}
