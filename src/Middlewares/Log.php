<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Record\RecordInterface;
use Psr\Log\LoggerInterface;

class Log implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $log;

    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->log->info('Processing kafka record: '.$record->getPayload(), [
            'original' => (array) $record->getOriginal(),
        ]);

        $handler->handle($record);
    }
}
