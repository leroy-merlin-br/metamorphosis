<?php
namespace Metamorphosis\TopicHandler\Producer;

use Metamorphosis\Record\ConsumerRecord as ProduceRecord;

abstract class AbstractHandler
{
    private $record;

    private $topic;

    private $partition;

    private $key;

    public function __construct($record, $topic, $partition, $key)
    {
        $this->record = $record;
        $this->topic = $topic;
        $this->partition = $partition;
        $this->key = $key;
    }

    public function produce(ProduceRecord $record): void
    {
        //$record->getPayload();
        //$record->getKey();
        //$record->getPartition();
    }

    public function getRecord()
    {
        return $this->record;
    }

    public function getPartition()
    {
        return $this->partition;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getTopic()
    {
        return $this->topic;
    }
}
