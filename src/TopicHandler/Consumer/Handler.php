<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Message;

interface Handler
{
    /**
     * Handle message.
     *
     * @param Message $message
     */
    public function handle(Message $message): void;

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
