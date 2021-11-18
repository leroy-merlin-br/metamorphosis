<?php
namespace Metamorphosis\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;

class Iterator extends AbstractMiddlewareHandler
{
    public function handle(RecordInterface $record): void
    {
        $closure = Closure::fromCallable([$this, 'handle']);
        $entry = current($this->queue);
        $middleware = $entry;
        next($this->queue);

        if ($middleware instanceof MiddlewareInterface) {
            $middleware->process($record, $closure);
        }
    }
}
