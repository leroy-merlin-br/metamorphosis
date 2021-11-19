<?php
namespace Metamorphosis\Middlewares;

use Closure;
use Metamorphosis\Record\RecordInterface;

interface MiddlewareInterface
{
    /**
     * @return mixed
     */
    public function process(RecordInterface $record, Closure $next);
}
