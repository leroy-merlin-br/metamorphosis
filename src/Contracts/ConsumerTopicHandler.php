<?php
namespace Metamorphosis\Contracts;

use Exception;
use Metamorphosis\Message;

interface ConsumerTopicHandler
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
     * The failure to process.
     *
     * @param Exception $exception
     */
    public function failed(Exception $exception): void;
}
