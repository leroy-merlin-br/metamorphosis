<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record;
use Metamorphosis\Middlewares\Middleware;

class Iterator extends AbstractMiddlewareHandler
{
    public function handle(Record $record): void
    {
        $entry = current($this->queue);
        $middleware = is_string($entry) ? app($entry) : $entry;
        next($this->queue);

        if ($middleware instanceof Middleware) {
            $middleware->process($record, $this);

            return;
        }

        $middleware($record, $this);
    }
}
