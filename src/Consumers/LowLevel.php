<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Config;
use RdKafka\ConsumerTopic;
use RdKafka\Message;

class LowLevel implements ConsumerInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ConsumerTopic
     */
    protected $consumer;

    public function __construct(Config $config, ConsumerTopic $consumer)
    {
        $this->config = $config;
        $this->consumer = $consumer;
    }

    /**
     * @param int $timeout
     *
     * @return Message
     */
    public function consume(int $timeout): Message
    {
        return $this->consumer->consume($this->config->getConsumerPartition(), $timeout);
    }
}
