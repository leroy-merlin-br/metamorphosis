<?php

namespace Metamorphosis\TopicHandler\Producer;

use RdKafka\Message;

interface HandleableResponseInterface
{
    public function success(Message $message): void;

    public function failed(Message $message): void;
}
