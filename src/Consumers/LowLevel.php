<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Config\Consumer as ConsumerConfig;
use RdKafka\ConsumerTopic;
use RdKafka\Message;

class LowLevel implements ConsumerInterface
{
    /**
     * @var ConsumerConfig
     */
    protected $config;

    /**
     * @var ConsumerTopic
     */
    protected $consumer;

    public function __construct(ConsumerConfig $config, ConsumerTopic $consumer)
    {
        $this->config = $config;
        $this->consumer = $consumer;
    }

    public function consume(int $timeout): Message
    {
        return $this->consumer->consume($this->config->getConsumerPartition(), $timeout);
    }
}
