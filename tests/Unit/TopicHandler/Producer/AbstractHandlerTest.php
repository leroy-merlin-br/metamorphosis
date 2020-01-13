<?php
namespace Tests\Unit\TopicHandler\Producer;

use Metamorphosis\Facades\Manager;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\AbstractHandler;
use Tests\LaravelTestCase;

class AbstractHandlerTest extends LaravelTestCase
{
    public function testShouldCreateRecord(): void
    {
        // Set
        $record = '1';
        $topic = 'default';
        $key = 'default_1';
        $partition = 1;
        $handler = new class($record, $topic, $key, $partition) extends AbstractHandler {
        };

        // Actions
        $result = $handler->createRecord();

        // Assertions
        $this->assertInstanceOf(ProducerRecord::class, $result);
        $this->assertSame($key, $result->getKey());
        $this->assertSame($topic, $result->getTopicName());
        $this->assertSame($record, $result->getPayload());
        $this->assertSame($partition, $result->getPartition());
    }

    public function testShouldCreateEncodeJsonWhenRecordIsArray(): void
    {
        // Set
        $record = ['number' => 1];
        $topic = 'default';
        $key = 'default_1';
        $partition = 1;
        $handler = new class($record, $topic, $key, $partition) extends AbstractHandler {
        };

        // Actions
        $result = $handler->createRecord();

        // Assertions
        $this->assertInstanceOf(ProducerRecord::class, $result);
        $this->assertSame($key, $result->getKey());
        $this->assertSame($topic, $result->getTopicName());
        $this->assertSame(json_encode($record), $result->getPayload());
        $this->assertSame($partition, $result->getPartition());
    }

    public function testShouldSetDefaultPartitionWhenIsNull(): void
    {
        // Set
        Manager::set(['partition' => RD_KAFKA_PARTITION_UA]);
        $record = '1';
        $topic = 'default';
        $key = 'default_1';
        $partition = null;
        $handler = new class($record, $topic, $key, $partition) extends AbstractHandler {};

        // Actions
        $result = $handler->createRecord();

        // Assertions
        $this->assertInstanceOf(ProducerRecord::class, $result);
        $this->assertSame($key, $result->getKey());
        $this->assertSame($topic, $result->getTopicName());
        $this->assertSame($record, $result->getPayload());
        $this->assertSame(RD_KAFKA_PARTITION_UA, $result->getPartition());
    }
}
