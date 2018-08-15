<?php
namespace Metamorphosis\Middlewares;

use Illuminate\Contracts\Logging\Log as BaseLog;
use Metamorphosis\Contracts\Middleware;
use Metamorphosis\Contracts\MiddlewareHandler;
use Metamorphosis\Message;

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
