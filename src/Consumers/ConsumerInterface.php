<?php
namespace Metamorphosis\Consumers;

use RdKafka\Message;

interface ConsumerInterface
{
    public function consume(int $timeout): Message;
}
