<?php
namespace Metamorphosis\Middlewares;

use Illuminate\Support\Facades\Log as BaseLog;
use Metamorphosis\Contracts\Middleware;
use Metamorphosis\Contracts\MiddlewareHandler;
use Metamorphosis\Message;

class Log implements Middleware
{
    public function process(Message $message, MiddlewareHandler $handler): void
    {
        BaseLog::info(print_r($message, true));

        $handler->handle($message);
    }
}
