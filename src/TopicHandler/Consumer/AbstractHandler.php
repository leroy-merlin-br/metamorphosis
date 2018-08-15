<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;

abstract class AbstractHandler implements Handler
{
    public function failed(Exception $exception): void
    {
    }
}
