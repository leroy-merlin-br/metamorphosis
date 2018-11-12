<?php
namespace Tests\Dummies;

use Metamorphosis\TopicHandler\Producer\AbstractHandler;

class SecondProducerHandlerDummy extends AbstractHandler
{
    public function __construct($record, ?string $topic = NULL, ?string $key = NULL, ?int $partition = NULL)
    {
        $this->record = $record;
        $this->topic = $topic;
        $this->key = $key;
        $this->partition = $partition;
    }
}
