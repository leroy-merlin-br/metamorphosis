<?php
namespace Tests\Middlewares;

use Illuminate\Contracts\Logging\Log as BaseLog;
use Metamorphosis\Message;
use Metamorphosis\Middlewares\Iterator;
use Metamorphosis\Middlewares\Log;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class LogTest extends LaravelTestCase
{
    /** @test */
    public function it_should_log_message()
    {
        $log = $this->getMockBuilder(BaseLog::class)
            ->disableOriginalConstructor()
            ->setMethods(['info'])
            ->getMock();

        $middleware = new Log($log);

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = 'original message';
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);

        $handler = $this->getMockBuilder(Iterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $log->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Processing kafka message: original message'),
                $this->equalTo(['original' => [
                    'err' => RD_KAFKA_RESP_ERR_NO_ERROR,
                    'topic_name' => null,
                    'partition' => null,
                    'payload' => 'original message',
                    'len' => null,
                    'key' => null,
                    'offset' => null,
                ]])
            );

        $middleware->process($message, $handler);
    }
}
