<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
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
     * Handle warning exceptions.
     *
     * @param ResponseWarningException $exception
     */
    public function warning(ResponseWarningException $exception): void;

    /**
     * Handle failure process.
     *
     * @param Exception $exception
     */
    public function failed(Exception $exception): void;
}
