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
    public function testItShouldDecodeAndUpdateMessagePayload()
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

    public function testItShouldThrowAnExceptionOnInvalidJsonString()
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
