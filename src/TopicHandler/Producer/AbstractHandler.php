<?php
namespace Metamorphosis\TopicHandler\Producer;

abstract class AbstractHandler implements HandlerInterface
{
    protected $record;

    protected $topic;

    protected $key;

    protected $partition;

    public function __construct($record, string $topic = null, string $key = null, int $partition = null)
    {
        $this->record = $record;
        $this->topic = $topic;
        $this->key = $key;
        $this->partition = $partition;
    }

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
