<?php
namespace Tests\Middlewares;

use Avro\DataIO\DataIOWriter;
use Avro\Datum\IODatumWriter;
use Avro\IO\StringIO;
use Avro\Schema\Schema;
use Metamorphosis\Message;
use Metamorphosis\Middlewares\AvroDecode;
use Metamorphosis\Middlewares\Handler\Iterator;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class AvroDecodeTest extends LaravelTestCase
{
    /** @test */
    public function it_should_decode_and_update_message_payload()
    {
        $jose = ['member_id' => 1392, 'member_name' => 'Jose'];
        $maria = ['member_id' => 1642, 'member_name' => 'Maria'];
        $data = [$jose, $maria];

        $binaryString = $this->produceBinaryString($data);

        $middleware = new AvroDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $binaryString;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $message = new Message($kafkaMessage);

        $handler = $this->createMock(Iterator::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($message));

        $middleware->process($message, $handler);

        $this->assertSame($data, $message->getPayload());
    }

    public function produceBinaryString($data)
    {
        $schemaJson = <<<_JSON
{
"name":"member",
"type":"record",
"fields":
    [
        {"name":"member_id", "type":"int"},
        {"name":"member_name", "type":"string"}
    ]
}
_JSON;

        $io = new StringIO();
        $writers_schema = Schema::parse($schemaJson);

        $data_writer = new DataIOWriter(
            $io,
            new IODatumWriter($writers_schema),
            $writers_schema
        );

        foreach ($data as $datum) {
            $data_writer->append($datum);
        }

        $data_writer->close();

        return $io->string();
    }
}
