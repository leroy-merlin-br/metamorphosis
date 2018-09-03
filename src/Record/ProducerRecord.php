<?php
namespace Metamorphosis\Record;

class ProducerRecord implements Record
{
    protected $payload;

    protected $key;

    protected $partition;

    protected $original;

    protected $topic;

    public function __construct($payload, $topic, $partition = null, $key = null)
    {
        $this->payload = $payload;
        $this->original = $payload;
        $this->partition = $partition;
        $this->topic = $topic;
        $this->key = $key;
    }

    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getTopicName(): string
    {
        return $this->topic;
    }

    public function getPartition(): int
    {
        return $this->partition;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOriginal()
    {
        return $this->original;
    }
}
