<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
use Metamorphosis\Record\Record;
use Psr\Log\LoggerInterface;

class Log implements Middleware
{
    /**
     * @var LoggerInterface
     */
    protected $log;

    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }

    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $this->log->info('Processing kafka record: '.$record->getPayload(), [
            'original' => (array) $record->getOriginal(),
        ]);

        $handler->handle($record);
    }
}
