<?php
namespace Tests;

use Metamorphosis\Exceptions\ResponseErrorException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record;
use RdKafka\Message;

class RecordTest extends LaravelTestCase
{
    /** @test */
    public function it_should_build_record()
    {
        $message = new Message();
        $payload = 'some payload';
        $topic = 'some-topic-name';
        $partition = 1;
        $offset = 1;
        $key = 'some-key';

        $message->payload = $payload;
        $message->topic_name = $topic;
        $message->partition = $partition;
        $message->key = $key;
        $message->offset = $offset;
        $message->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($message);

        $this->assertSame($payload, $record->getPayload());
        $this->assertSame($message, $record->getOriginal());
        $this->assertSame($topic, $record->getTopicName());
        $this->assertSame($partition, $record->getPartition());
        $this->assertSame($offset, $record->getOffset());
        $this->assertSame($key, $record->getKey());
    }

    /** @test */
    public function it_should_throw_response_error_exception_when_message_has_errors()
    {
        $message = new Message();

        $message->payload = 'some payload';
        $message->topic_name = 'some-topic-name';
        $message->partition = 1;
        $message->key = 1;
        $message->offset = 1;

        $this->expectException(ResponseErrorException::class);

        new Record($message);
    }

    /** @test */
    public function it_should_throw_response_warning_exception_when_message_has_errors()
    {
        $message = new Message();

        $message->payload = 'some payload';
        $message->topic_name = 'some-topic-name';
        $message->partition = 1;
        $message->key = 1;
        $message->offset = 1;
        $message->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        $this->expectException(ResponseWarningException::class);

        new Record($message);
    }
}
