<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Message;

interface Handler
{
    /**
     * Handle payload.
     *
     * @param Message $message
     *
     * @return bool
     */
    public function handle(Message $message): bool;

    /**
     * Handle failure process.
     *
     * @param Exception $exception
     */
    public function failed(Exception $exception): void;
}
