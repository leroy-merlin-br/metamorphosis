<?php
namespace Tests\Record;

use Metamorphosis\Record\ProducerRecord as Record;
use Tests\LaravelTestCase;

class ProducerRecordTest extends LaravelTestCase
{
    /** @test */
    public function it_should_get_message()
    {
        $message = ['message' => 'original record'];
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($message, $record->getPayload());
    }

    /** @test */
    public function it_should_get_original_message()
    {
        $message = ['message' => 'original record'];
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($message, $record->getOriginal());
    }

    /** @test */
    public function it_should_get_topic_name()
    {
        $message = ['message' => 'original record'];
        $topicName = 'some-topic';

        $record = new Record($message, $topicName);

        $this->assertSame($topicName, $record->getTopicName());
    }

    /** @test */
    public function it_should_get_partition()
    {
        $message = ['message' => 'original record'];
        $topicName = 'some-topic';
        $partition = 0;

        $record = new Record($message, $topicName, $partition);

        $this->assertSame($partition, $record->getPartition());
    }

    /** @test */
    public function it_should_get_key()
    {
        $message = ['message' => 'original record'];
        $topicName = 'some-topic';
        $partition = 0;
        $key = 'message-key';

        $record = new Record($message, $topicName, $partition, $key);

        $this->assertSame($key, $record->getKey());
    }
}
