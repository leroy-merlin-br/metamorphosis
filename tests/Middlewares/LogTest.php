<?php
namespace Tests\Middlewares;

use Psr\Log\LoggerInterface;
use Metamorphosis\Record\ConsumerRecord as Record;
use Metamorphosis\Middlewares\Handler\Iterator;
use Metamorphosis\Middlewares\Log;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class LogTest extends LaravelTestCase
{
    /** @test */
    public function it_should_log_message()
    {
        $log = $this->createMock(LoggerInterface::class);

        $middleware = new Log($log);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $handler = $this->createMock(Iterator::class);

        $log->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Processing kafka record: original record'),
                $this->equalTo(['original' => [
                    'err' => RD_KAFKA_RESP_ERR_NO_ERROR,
                    'topic_name' => null,
                    'partition' => null,
                    'payload' => 'original record',
                    'len' => null,
                    'key' => null,
                    'offset' => null,
                ]])
            );

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($record));

        $middleware->process($record, $handler);
    }
}
