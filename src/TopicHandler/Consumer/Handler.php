<?php

namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\RecordInterface;

interface Handler
{
    /**
     * Handle record.
     */
    public function handle(RecordInterface $record): void;

    /**
     * Handle warning exceptions.
     */
    public function warning(ResponseWarningException $exception): void;

    /**
     * Handle finished consume.
     */
    public function finished(): void;

    /**
     * Handle failure process.
     */
    public function failed(Exception $exception): void;
}
