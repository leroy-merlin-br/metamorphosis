<?php
namespace Metamorphosis\Middlewares;

use Illuminate\Contracts\Logging\Log as BaseLog;
use Metamorphosis\Message;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;

class Log implements Middleware
{
    /**
     * @var BaseLog
     */
    protected $log;

    public function __construct(BaseLog $log)
    {
        $this->log = $log;
    }

    public function process(Message $message, MiddlewareHandler $handler): void
    {
        $this->log->info('Processing kafka message: '.$message->getPayload(), [
            'original' => (array) $message->getOriginal(),
        ]);

        $handler->handle($message);
    }
}
