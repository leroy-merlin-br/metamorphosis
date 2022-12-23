<?php

namespace Tests\Unit\Middlewares;

use Closure;
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
        $closure = Closure::fromCallable(function ($record) {
            return $record;
        });

        // Expectations
        $log->expects()
            ->info('Processing kafka record: original record', m::on(
                static function (array $context): bool {
                    $original = $context['original'];
                    $expected = [
                        'err' => RD_KAFKA_RESP_ERR_NO_ERROR,
                        'topic_name' => null,
                        'timestamp' => null,
                        'payload' => 'original record',
                        'len' => null,
                        'key' => null,
                        'opaque' => null,
                    ];

                    foreach ($expected as $key => $expectedValue) {
                        if (!array_key_exists($key, $original)) {
                            return false;
                        }

                        if ($original[$key] !== $expectedValue) {
                            return false;
                        }
                    }

                    return true;
                }
            ));

        // Actions
        $middleware->process($record, $closure);
    }
}
