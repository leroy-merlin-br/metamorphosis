<?php

namespace Tests\Unit\Dummies;

use Metamorphosis\TopicHandler\Producer\AbstractHandler;

class SecondProducerHandlerDummy extends AbstractHandler
{
    public function __construct($record, string $topic, ?string $key = null, ?int $partition = null)
    {
        $this->record = $record;
        $this->topic = $topic;
        $this->key = $key;
        $this->partition = $partition;
    }
}
