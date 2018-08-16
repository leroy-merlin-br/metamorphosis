<?php
namespace Tests;

use Metamorphosis\Exceptions\KafkaResponseErrorException;
use Metamorphosis\Exceptions\KafkaResponseHandleableErrorException;
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

        $this->expectException(KafkaResponseErrorException::class);
        $this->expectExceptionMessage('Invalid message. Error code: '.RD_KAFKA_RESP_ERR_INVALID_MSG);

        new Message($kafkaMessage);
    }

    /** @test */
    public function it_should_throw_handleable_error_exception()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        $this->expectException(KafkaResponseHandleableErrorException::class);
        $this->expectExceptionMessage('Invalid message. Error code: '.RD_KAFKA_RESP_ERR__PARTITION_EOF);

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
}
