<?php
namespace Tests\Middlewares;

use Exception;
use Metamorphosis\Middlewares\Handler\Iterator;
use Metamorphosis\Middlewares\JsonDecode;
use Metamorphosis\Record\ConsumerRecord;
use Mockery as m;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class JsonDecodeTest extends LaravelTestCase
{
    public function testItShouldDecodeAndUpdateMessagePayload(): void
    {
        // Set
        $data = [['member_id' => 1392, 'member_name' => 'Jose']];
        $json = json_encode($data);
        $middleware = new JsonDecode();
        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $json;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $record = new ConsumerRecord($kafkaMessage);
        $handler = m::mock(Iterator::class);

        // Expectations
        $handler->expects()
            ->handle($record);

        // Actions
        $middleware->process($record, $handler);

        // Assertions
        $this->assertSame($data, $record->getPayload());
    }

    public function testItShouldThrowAnExceptionOnInvalidJsonString(): void
    {
        // Set
        $handler = m::mock(Iterator::class);
        $json = "{'Organization': 'Metamorphosis Team'}";

        $middleware = new JsonDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $json;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new ConsumerRecord($kafkaMessage);

        // Expectations
        $handler->expects()
            ->handle()
            ->never();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Malformed JSON. Error: Syntax error');

        // Actions
        $middleware->process($record, $handler);
    }
}
