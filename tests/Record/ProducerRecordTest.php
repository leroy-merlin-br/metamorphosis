<?php
namespace Tests\Record;

use Metamorphosis\Record\ProducerRecord as Record;
use Tests\LaravelTestCase;

class ProducerRecordTest extends LaravelTestCase
{
    public function testItShouldGetMessage(): void
    {
        // Set
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some_topic';

        // Actions
        $record = new Record($message, $topicName);

        // Assertions
        $this->assertSame($message, $record->getPayload());
    }

    public function testItShouldGetOriginalMessage(): void
    {
        // Set
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some_topic';

        // Actions
        $record = new Record($message, $topicName);

        // Assertions
        $this->assertSame($message, $record->getOriginal());
    }

    public function testItShouldGetTopicName(): void
    {
        // Set
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some_topic';

        // Actions
        $record = new Record($message, $topicName);

        // Assertions
        $this->assertSame($topicName, $record->getTopicName());
    }

    public function testItShouldGetPartition(): void
    {
        // Set
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some_topic';
        $partition = 0;

        // Actions
        $record = new Record($message, $topicName, $partition);

        // Assertions
        $this->assertSame($partition, $record->getPartition());
    }

    public function testItShouldGetKey(): void
    {
        // Set
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some_topic';
        $partition = 0;
        $key = 'message-key';

        // Actions
        $record = new Record($message, $topicName, $partition, $key);

        // Assertions
        $this->assertSame($key, $record->getKey());
    }

    public function testItShouldOverridePayload(): void
    {
        // Set
        $originalMessage = json_encode(['message' => 'original record']);
        $changedMessage = json_encode(['message' => 'changed record']);
        $topicName = 'some_topic';

        // Actions
        $record = new Record($originalMessage, $topicName);

        $record->setPayload($changedMessage);

        // Assertions
        $this->assertSame($originalMessage, $record->getOriginal());
        $this->assertSame($changedMessage, $record->getPayload());
    }
}
