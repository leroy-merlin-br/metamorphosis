<?php
namespace Metamorphosis\Middlewares;

use Avro\DataIO\DataIOReader;
use Avro\Datum\IODatumReader;
use Avro\IO\StringIO;
use Metamorphosis\Contracts\Middleware;
use Metamorphosis\Contracts\MiddlewareHandler;
use Metamorphosis\Message;

class AvroDecode implements Middleware
{
    public function process(Message $message, MiddlewareHandler $handler): void
    {
        $readIo = new StringIO($message->getPayload());
        $dataReader = new DataIOReader($readIo, new IODatumReader());

        $decodedMessage = '';

        foreach ($dataReader->data() as $datum) {
            $decodedMessage .= $datum;
        }

        $message->setPayload($decodedMessage);

        $handler->handle($message);
    }
}
