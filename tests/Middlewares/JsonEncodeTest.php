<?php
namespace Tests\Middlewares;

use Metamorphosis\Middlewares\JsonEncode;
use Metamorphosis\Middlewares\Handler\Iterator;
use Metamorphosis\Record\ProducerRecord;
use Tests\LaravelTestCase;

class JsonEncodeTest extends LaravelTestCase
{
    /** @test */
    public function it_should_encode_and_update_message_payload()
    {
        $data = [['member_id' => 1392, 'member_name' => 'Jose']];

        $jsonData = json_encode($data);

        $middleware = new JsonEncode();

        $record = new ProducerRecord($data, 'some-topic');

        $handler = $this->createMock(Iterator::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($record));

        $middleware->process($record, $handler);

        $this->assertSame($jsonData, $record->getPayload());
    }
}
