<?php
namespace Metamorphosis\Middlewares;

use Exception;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
use Metamorphosis\Record\Record;

class JsonDecode implements Middleware
{
    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $payload = json_decode($record->getPayload(), true);

        if ($payload === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(
                'Malformed JSON. Error: '.json_last_error_msg()
            );
        }

        $record->setPayload($payload);

        $handler->handle($record);
    }
}
