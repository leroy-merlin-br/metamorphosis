<?php

namespace Tests\Unit\TopicHandler\Producer;

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
        $partition = 0;
        $handler = new class ($record, $topic, $key, $partition) extends AbstractHandler {
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
        $record = ['number' => 1, 'float' => 0.0];
        $topic = 'default';
        $key = 'default_1';
        $partition = 1;
        $handler = new class ($record, $topic, $key, $partition) extends AbstractHandler {
        };

        // Actions
        $result = $handler->createRecord();

        // Assertions
        $this->assertInstanceOf(ProducerRecord::class, $result);
        $this->assertSame($key, $result->getKey());
        $this->assertSame($topic, $result->getTopicName());
        $this->assertSame('{"number":1,"float":0.0}', $result->getPayload());
        $this->assertSame($partition, $result->getPartition());
    }
}
