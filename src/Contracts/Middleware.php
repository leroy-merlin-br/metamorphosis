<?php
namespace Metamorphosis\Contracts;

use Metamorphosis\Message;

interface Middleware
{
    /**
     * Handle payload.
     *
     * @param Message $message
     *
     * @return Message
     */
    public function process(Message $message): Message;
}
