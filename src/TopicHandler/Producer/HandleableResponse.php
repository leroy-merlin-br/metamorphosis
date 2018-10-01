<?php
namespace Metamorphosis\TopicHandler\Producer;

use RdKafka\Message;

interface HandleableResponse
{
    public function success(Message $message): void;

    public function failed(Message $message): void;
}
