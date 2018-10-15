<?php
namespace Metamorphosis\TopicHandler\Producer;

abstract class AbstractHandler implements HandlerInterface
{
    protected $record;

    protected $topic;

    protected $key;

    protected $partition;

    public function getRecord()
    {
        return $this->record;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getPartition(): ?int
    {
        return $this->partition;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }
}
