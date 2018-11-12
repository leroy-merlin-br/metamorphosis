<?php
namespace Tests\Dummies;

use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use RdKafka\Message;

class ProducerHandlerDummy extends AbstractHandler implements HandleableResponseInterface
{
    public function __construct($record, ?string $topic = NULL, ?string $key = NULL, ?int $partition = NULL)
    {
        $this->record = $record;
        $this->topic = $topic;
        $this->key = $key;
        $this->partition = $partition;
    }

    public function success(Message $message): void
    {
        dump('success!');
    }

    public function failed(Message $message): void
    {
        dump('failed!');
    }
}
