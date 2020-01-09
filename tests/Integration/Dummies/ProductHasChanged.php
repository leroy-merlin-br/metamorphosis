<?php
namespace Tests\Integration\Dummies;

use Illuminate\Support\Facades\Log;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use RdKafka\Message;
use RuntimeException;

class ProductHasChanged extends AbstractHandler
{
    /**
     * @var string
     */
    public $topic = 'default';

    public function __construct($record, string $topic = null, string $key = null, int $partition = null)
    {
        $this->record = $record;
        $this->key = 'recordId123';
    }

    public function success(Message $message): void
    {
        Log::info('Record successfully sent to kafka.', [
            'topic' => $message->topic_name,
            'payload' => $message->payload,
            'key' => $message->key,
            'partition' => $message->partition,
        ]);
    }

    public function failed(Message $message): void
    {
        Log::error('Unable to delivery record into kafka.', [
            'topic' => $message->topic_name,
            'payload' => $message->payload,
            'key' => $message->key,
            'partition' => $message->partition,
            'error' => $message->err,
        ]);

        throw new RuntimeException('error when sending kafka message!');
    }
}
