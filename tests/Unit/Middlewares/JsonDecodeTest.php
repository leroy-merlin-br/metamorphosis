<?php
namespace Tests\Unit\Middlewares;

use Closure;
use Exception;
use Metamorphosis\Middlewares\JsonDecode;
use Metamorphosis\Record\ConsumerRecord;
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
        $closure = Closure::fromCallable(function ($record) {
            return $record;
        });

        // Actions
        $record = $middleware->process($record, $closure);

        // Assertions
        $this->assertSame($data, $record->getPayload());
    }

    public function testItShouldThrowAnExceptionOnInvalidJsonString(): void
    {
        // Set
        $json = "{'Organization': 'Metamorphosis Team'}";
        $closure = Closure::fromCallable(function () {
        });
        $middleware = new JsonDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $json;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new ConsumerRecord($kafkaMessage);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Malformed JSON. Error: Syntax error');

        // Actions
        $middleware->process($record, $closure);
    }
}
