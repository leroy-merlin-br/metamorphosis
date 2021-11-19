<?php
namespace Metamorphosis\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;

class Iterator extends AbstractMiddlewareHandler
{
    public function handle(RecordInterface $record)
    {
        $closure = Closure::fromCallable([$this, 'handle']);
        $entry = current($this->queue);
        $middleware = $entry;
        next($this->queue);

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($record, $closure);
        }

        return $record;
    }
}
