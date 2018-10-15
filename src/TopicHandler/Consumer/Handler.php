<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\RecordInterface;

interface Handler
{
    /**
     * Handle record.
     *
     * @param RecordInterface $record
     */
    public function handle(RecordInterface $record): void;

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
