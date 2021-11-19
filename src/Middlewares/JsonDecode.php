<?php
namespace Metamorphosis\Middlewares;

use Closure;
use Exception;
use Metamorphosis\Record\RecordInterface;

class JsonDecode implements MiddlewareInterface
{
    public function process(RecordInterface $record, Closure $next)
    {
        $payload = json_decode($record->getPayload(), true);

        if (null === $payload && JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception(
                'Malformed JSON. Error: '.json_last_error_msg()
            );
        }

        $record->setPayload($payload);

        return $next($record);
    }
}
