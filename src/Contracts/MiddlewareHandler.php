<?php
namespace Metamorphosis\Contracts;

use Metamorphosis\Message;

interface MiddlewareHandler
{
    /**
     * @param Message $message
     */
    public function handle(Message $message): void;
}
