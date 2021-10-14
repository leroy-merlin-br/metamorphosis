<?php
namespace Tests\Integration\Dummies;

use Illuminate\Support\Facades\Log;
use Metamorphosis\TopicHandler\Producer\AbstractProducer;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use RdKafka\Message;
use RuntimeException;

class MessageProducerWithConfigOptions extends AbstractProducer implements HandleableResponseInterface
{
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
