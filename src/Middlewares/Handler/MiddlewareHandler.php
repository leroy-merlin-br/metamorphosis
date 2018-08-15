<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Message;

interface MiddlewareHandler
{
    /**
     * @param Message $message
     */
    public function handle(Message $message): void;
}
