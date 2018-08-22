<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Config;
use Metamorphosis\Record;
use RdKafka\ConsumerTopic;

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
     * @return Record
     */
    public function consume(int $timeout): Record
    {
        return $this->consumer->consume($config->getPartition(), $timeout);
    }
}
