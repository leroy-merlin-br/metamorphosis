<?php
namespace Tests\Middlewares;

use Avro\DataIO\DataIOWriter;
use Avro\Datum\IODatumWriter;
use Avro\Exception\DataIoException;
use Avro\IO\StringIO;
use Avro\Schema\Schema;
use Metamorphosis\Middlewares\AvroDecode;
use Metamorphosis\Middlewares\Handler\Iterator;
use Metamorphosis\Record\ConsumerRecord as Record;
use RdKafka\Message as KafkaMessage;
use Tests\LaravelTestCase;

class AvroDecodeTest extends LaravelTestCase
{
    public function testItShouldDecodeAndUpdateMessagePayload()
    {
        $data = [['member_id' => 1392, 'member_name' => 'Jose']];

        $binaryString = $this->produceBinaryString($data);

        $middleware = new AvroDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $binaryString;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $handler = $this->createMock(Iterator::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($record));

        $middleware->process($record, $handler);

        $this->assertSame($data, $record->getPayload());
    }

    public function testItShouldDecodeAndUpdateMessagePayloadWithMultiplesRecords()
    {
        $jose = ['member_id' => 1392, 'member_name' => 'Jose'];
        $maria = ['member_id' => 1642, 'member_name' => 'Maria'];
        $data = [$jose, $maria];

        $binaryString = $this->produceBinaryString($data);

        $middleware = new AvroDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $binaryString;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $handler = $this->createMock(Iterator::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($record));

        $middleware->process($record, $handler);

        $this->assertSame($data, $record->getPayload());
    }

    public function testItShouldThrowAnExceptionOnInvalidBinaryString()
    {
        $binaryString = 'invalid-binary-string';

        $middleware = new AvroDecode();

        $kafkaMessage = new KafkaMessage();
        $kafkaMessage->payload = $binaryString;
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $record = new Record($kafkaMessage);

        $handler = $this->createMock(Iterator::class);

        $handler->expects($this->never())
            ->method('handle');

        $this->expectException(DataIoException::class);

        $middleware->process($record, $handler);
    }

    private function produceBinaryString($data)
    {
        $schemaJson = <<<'_JSON'
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
