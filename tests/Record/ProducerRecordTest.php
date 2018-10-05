<?php
namespace Tests\Record;

use Metamorphosis\Record\ProducerRecord as Record;
use Tests\LaravelTestCase;

class ProducerRecordTest extends LaravelTestCase
{
    public function testItShouldGetMessage()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($message, $record->getPayload());
    }

    public function testItShouldGetOriginalMessage()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($message, $record->getOriginal());
    }

    public function testItShouldGetTopicName()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($topicName, $record->getTopicName());
    }

    public function testItShouldGetPartition()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';
        $partition = 0;

        $record = new Record($message, $topicName, $partition);

        $this->assertSame($partition, $record->getPartition());
    }

    public function testItShouldGetKey()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';
        $partition = 0;
        $key = 'message-key';

        $record = new Record($message, $topicName, $partition, $key);

        $this->assertSame($key, $record->getKey());
    }

    public function testItShouldOverridePayload()
    {
        $originalMessage = json_encode(['message' => 'original record']);
        $changedMessage = json_encode(['message' => 'changed record']);
        $topicName = 'some-topic';

        $record = new Record($originalMessage, $topicName);

        $record->setPayload($changedMessage);

        $this->assertSame($originalMessage, $record->getOriginal());
        $this->assertSame($changedMessage, $record->getPayload());
    }
}
