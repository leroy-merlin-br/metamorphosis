<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Message;

class Dispatcher extends AbstractMiddlewareHandler
{
    public function handle(Message $message): void
    {
        reset($this->queue);
        $iterator = new Iterator($this->queue);
        $iterator->handle($message);
    }
}
