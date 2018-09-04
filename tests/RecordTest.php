<?php
namespace Tests;

use Metamorphosis\Exceptions\ResponseErrorException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\ConsumerRecord as Record;
use RdKafka\Message as KafkaMessage;

class RecordTest extends LaravelTestCase
{
    /** @test */
    public function it_should_throw_exception_when_base_message_has_errors()
    {
        $kafkaMessage = $this->createMock(KafkaMessage::class);
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_INVALID_MSG;

        $kafkaMessage->method('errstr')
             ->willReturn('Invalid Message');

        $this->expectException(ResponseErrorException::class);
        $this->expectExceptionMessage('Error response: Invalid Message');
        $this->expectExceptionCode(RD_KAFKA_RESP_ERR_INVALID_MSG);

        new Record($kafkaMessage);
    }

    /** @test */
    public function it_should_throw_warning_exception()
    {
        $kafkaMessage = $this->createMock(KafkaMessage::class);
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        $kafkaMessage->method('errstr')
             ->willReturn('Partition EOF');

        $this->expectException(ResponseWarningException::class);
        $this->expectExceptionMessage('Invalid response: Partition EOF');
        $this->expectExceptionCode(RD_KAFKA_RESP_ERR__PARTITION_EOF);

        new Record($kafkaMessage);
    }

    /** @test */
    public function it_should_provides_getter_and_setter_for_payload()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);
        $record->setPayload('new record');

        $this->assertSame('new record', $record->getPayload());
    }

    /** @test */
    public function it_should_get_original_message()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame($kafkaMessage, $record->getOriginal());
    }

    /** @test */
    public function it_should_get_topic_name()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->topic_name = 'topic-name';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame('topic-name', $record->getTopicName());
    }

    /** @test */
    public function it_should_get_partition()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->partition = 0;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame(0, $record->getPartition());
    }

    /** @test */
    public function it_should_get_key()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->key = 'key';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame('key', $record->getKey());
    }

    /** @test */
    public function it_should_get_offset()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->offset = 10;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame(10, $record->getOffset());
    }
}
