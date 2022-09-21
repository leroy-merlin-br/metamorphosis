<?php

namespace Tests\Integration\Dummies;

use Illuminate\Support\Facades\Log;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use RdKafka\Message;
use RuntimeException;

class MessageProducer extends AbstractHandler
{
    public string $topic = 'default';

    public function __construct($record, string $topic, ?string $key = null, ?int $partition = null)
    {
        $this->record = $record;
        $this->topic = $topic;
        $this->key = $key ?? 'recordId123';
        $this->partition = $partition;
    }

    public function success(Message $message): void
    {
        Log::info('Record successfully sent to broker.', [
            'topic' => $message->topic_name,
            'payload' => $message->payload,
            'key' => $message->key,
            'partition' => $message->partition,
        ]);
    }

    public function failed(Message $message): void
    {
        Log::error('Unable to delivery record to broker.', [
            'topic' => $message->topic_name,
            'payload' => $message->payload,
            'key' => $message->key,
            'partition' => $message->partition,
            'error' => $message->err,
        ]);

        throw new RuntimeException('error sending message!');
    }
}
