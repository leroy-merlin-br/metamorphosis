<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\Record;

interface Handler
{
    /**
     * Handle record.
     *
     * @param Record $record
     */
    public function handle(Record $record): void;

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
