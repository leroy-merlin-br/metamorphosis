<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Record\Record;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;

class JsonEncode implements Middleware
{
    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $jsonString = json_encode($record->getPayload());

        $record->setPayload($jsonString);

        $handler->handle($record);
    }
}
