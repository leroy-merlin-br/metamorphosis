<?php
namespace Tests\Record;

use Metamorphosis\Record\ProducerRecord as Record;
use Tests\LaravelTestCase;

class ProducerRecordTest extends LaravelTestCase
{
    /** @test */
    public function it_should_get_message()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($message, $record->getPayload());
    }

    /** @test */
    public function it_should_get_original_message()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($message, $record->getOriginal());
    }

    /** @test */
    public function it_should_get_topic_name()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($topicName, $record->getTopicName());
    }

    /** @test */
    public function it_should_get_partition()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';
        $partition = 0;

        $record = new Record($message, $topicName, $partition);

        $this->assertSame($partition, $record->getPartition());
    }

    /** @test */
    public function it_should_get_key()
    {
        $message = json_encode(['message' => 'original record']);
        $topicName = 'some-topic';
        $partition = 0;
        $key = 'message-key';

        $record = new Record($message, $topicName, $partition, $key);

        $this->assertSame($key, $record->getKey());
    }

    /** @test */
    public function it_should_override_payload()
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
