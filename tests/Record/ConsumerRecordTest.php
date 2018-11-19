<?php
namespace Tests\Record;

use Metamorphosis\Exceptions\ResponseErrorException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\ConsumerRecord as Record;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class ConsumerRecordTest extends LaravelTestCase
{
    public function testItShouldThrowExceptionWhenBaseMessageHasErrors()
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

    public function testItShouldThrowWarningException()
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

    public function testItShouldProvidesGetterAndSetterForPayload()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);
        $record->setPayload('new record');

        $this->assertSame('new record', $record->getPayload());
    }

    public function testItShouldGetOriginalMessage()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame($kafkaMessage, $record->getOriginal());
    }

    public function testItShouldGetTopicName()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->topic_name = 'topic-name';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame('topic-name', $record->getTopicName());
    }

    public function testItShouldGetPartition()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->partition = 0;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame(0, $record->getPartition());
    }

    public function testItShouldGetKey()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->key = 'key';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame('key', $record->getKey());
    }

    public function testItShouldGetOffset()
    {
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->offset = 10;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $this->assertSame(10, $record->getOffset());
    }
}
