<?php
namespace Tests\Middlewares;

use Exception;
use Metamorphosis\Middlewares\Handler\Iterator;
use Metamorphosis\Middlewares\JsonDecode;
use Metamorphosis\Record\ConsumerRecord;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class JsonDecodeTest extends LaravelTestCase
{
    /** @test */
    public function it_should_decode_and_update_message_payload()
    {
        $data = [['member_id' => 1392, 'member_name' => 'Jose']];

        $json = json_encode($data);

        $middleware = new JsonDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $json;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new ConsumerRecord($kafkaMessage);

        $handler = $this->createMock(Iterator::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($record));

        $middleware->process($record, $handler);

        $this->assertSame($data, $record->getPayload());
    }

    /** @test */
    public function it_should_throw_an_exception_on_invalid_json_string()
    {
        $json = "{'Organization': 'Metamorphosis Team'}";

        $middleware = new JsonDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $json;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new ConsumerRecord($kafkaMessage);

        $handler = $this->createMock(Iterator::class);

        $handler->expects($this->never())
            ->method('handle');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Malformed JSON. Error: Syntax error');

        $middleware->process($record, $handler);
    }
}
