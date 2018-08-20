<?php
namespace Metamorphosis\Middlewares;

use Avro\DataIO\DataIOReader;
use Avro\Datum\IODatumReader;
use Avro\IO\StringIO;
use Metamorphosis\Message;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;

class AvroDecode implements Middleware
{
    public function process(Message $message, MiddlewareHandler $handler): void
    {
        $readIo = new StringIO($message->getPayload());
        $dataReader = new DataIOReader($readIo, new IODatumReader());

        $records = [];
        foreach ($dataReader->data() as $record) {
            $records[] = $record;
        }

        $message->setPayload($records);

        $handler->handle($message);
    }
}
