<?php
namespace Tests\Unit\Record;

use Metamorphosis\Exceptions\ResponseErrorException;
use Metamorphosis\Exceptions\ResponseTimeoutException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\ConsumerRecord as Record;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class ConsumerRecordTest extends LaravelTestCase
{
    public function testItShouldThrowExceptionWhenBaseMessageHasErrors(): void
    {
        // Set
        $kafkaMessage = m::mock(KafkaMessage::class);
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_INVALID_MSG;

        // Expectations
        $kafkaMessage->expects()
            ->errstr()
            ->andReturn('Invalid Message');

        $this->expectException(ResponseErrorException::class);
        $this->expectExceptionMessage('Error response: Invalid Message');
        $this->expectExceptionCode(RD_KAFKA_RESP_ERR_INVALID_MSG);

        // Actions
        new Record($kafkaMessage);
    }

    public function testItShouldThrowTimeoutException(): void
    {
        // Set
        $kafkaMessage = m::mock(KafkaMessage::class);
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR__TIMED_OUT;

        // Expectations
        $kafkaMessage->expects()
            ->errstr()
            ->andReturn('Process timed out.');

        $this->expectException(ResponseTimeoutException::class);
        $this->expectExceptionMessage("Consumer finished to process or timed out: Process timed out.");
        $this->expectExceptionCode(RD_KAFKA_RESP_ERR__TIMED_OUT);

        // Actions
        new Record($kafkaMessage);
    }

    public function testItShouldThrowWarningException(): void
    {
        // Set
        $kafkaMessage = m::mock(KafkaMessage::class);
        $kafkaMessage->payload = '';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        // Expectations
        $kafkaMessage->expects()
            ->errstr()
            ->andReturn('Partition EOF');

        $this->expectException(ResponseWarningException::class);
        $this->expectExceptionMessage('Invalid response: Partition EOF');
        $this->expectExceptionCode(RD_KAFKA_RESP_ERR__PARTITION_EOF);

        // Actions
        new Record($kafkaMessage);
    }

    public function testItShouldProvidesGetterAndSetterForPayload(): void
    {
        // Set
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        // Actions
        $record = new Record($kafkaMessage);
        $record->setPayload('new record');

        // Expectations
        $this->assertSame('new record', $record->getPayload());
    }

    public function testItShouldGetOriginalMessage(): void
    {
        // Set
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        // Actions
        $record = new Record($kafkaMessage);

        // Assertions
        $this->assertSame($kafkaMessage, $record->getOriginal());
    }

    public function testItShouldGetTopicName(): void
    {
        // Set
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->topic_name = 'topic_name';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        // Actions
        $record = new Record($kafkaMessage);

        // Expectations
        $this->assertSame('topic_name', $record->getTopicName());
    }

    public function testItShouldGetPartition(): void
    {
        // Set
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->partition = 0;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        // Actions
        $record = new Record($kafkaMessage);

        // Expectations
        $this->assertSame(0, $record->getPartition());
    }

    public function testItShouldGetKey(): void
    {
        // Set
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->key = 'key';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        // Actions
        $record = new Record($kafkaMessage);

        // Assertions
        $this->assertSame('key', $record->getKey());
    }

    public function testItShouldGetOffset(): void
    {
        // Set
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->offset = 10;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        // Actions
        $record = new Record($kafkaMessage);

        // Assertions
        $this->assertSame(10, $record->getOffset());
    }
}
