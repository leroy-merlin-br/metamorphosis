<?php
namespace Tests;

use Metamorphosis\Exceptions\ResponseErrorException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Message;
use RdKafka\Message as KafkaMessage;

class MessageTest extends LaravelTestCase
{
    /** @test */
    public function it_should_throw_exception_when_base_message_has_errors()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_INVALID_MSG;

        $this->expectException(ResponseErrorException::class);
        $this->expectExceptionMessage('Error response.');
        $this->expectExceptionCode(RD_KAFKA_RESP_ERR_INVALID_MSG);

        new Message($kafkaMessage);
    }

    /** @test */
    public function it_should_throw_warning_exception()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        $this->expectException(ResponseWarningException::class);
        $this->expectExceptionMessage('Invalid response.');
        $this->expectExceptionCode(RD_KAFKA_RESP_ERR__PARTITION_EOF);

        new Message($kafkaMessage);
    }

    /** @test */
    public function it_should_provides_getter_and_setter_for_payload()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);
        $message->setPayload('new message');

        $this->assertSame('new message', $message->getPayload());
    }

    /** @test */
    public function it_should_get_original_message()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);

        $this->assertSame($kafkaMessage, $message->getOriginal());
    }

    /** @test */
    public function it_should_get_topic_name()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->topic_name = 'topic-name';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);

        $this->assertSame('topic-name', $message->getTopicName());
    }

    /** @test */
    public function it_should_get_partition()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->partition = 0;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);

        $this->assertSame(0, $message->getPartition());
    }

    /** @test */
    public function it_should_get_key()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->key = 'key';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);

        $this->assertSame('key', $message->getKey());
    }

    /** @test */
    public function it_should_get_offset()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->offset = 10;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);

        $this->assertSame(10, $message->getOffset());
    }
}
