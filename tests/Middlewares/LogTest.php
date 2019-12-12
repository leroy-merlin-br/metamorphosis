<?php
namespace Tests\Middlewares;

use Metamorphosis\Middlewares\Handler\Iterator;
use Metamorphosis\Middlewares\Log;
use Metamorphosis\Record\ConsumerRecord as Record;
use Mockery as m;
use Psr\Log\LoggerInterface;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class LogTest extends LaravelTestCase
{
    public function testItShouldLogMessage(): void
    {
        // Set
        $log = m::mock(LoggerInterface::class);
        $middleware = new Log($log);
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original record';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $record = new Record($kafkaMessage);
        $handler = m::mock(Iterator::class);

        // Expectations
        $log->expects()
            ->info('Processing kafka record: original record', [
                'original' => [
                    'err' => RD_KAFKA_RESP_ERR_NO_ERROR,
                    'topic_name' => null,
                    'partition' => null,
                    'payload' => 'original record',
                    'len' => null,
                    'key' => null,
                    'offset' => null,
                    'timestamp' => null,
                    'headers' => null,
                ],
            ]);

        $handler->expects()
            ->handle($record);

        // Actions
        $middleware->process($record, $handler);
    }
}
