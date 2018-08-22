<?php
namespace Metamorphosis\Consumers;

use RdKafka\Message;

interface ConsumerInterface
{
    /**
     * @param int $timeout
     *
     * @return Message
     */
    public function consume(int $timeout): Message;
}
