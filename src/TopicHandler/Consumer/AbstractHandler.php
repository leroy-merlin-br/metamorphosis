<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;

abstract class AbstractHandler implements Handler
{
    public function warning(ResponseWarningException $exception): void
    {
    }

    public function failed(Exception $exception): void
    {
    }

    public function finished(): void
    {
    }
}
