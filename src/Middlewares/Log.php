<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Message;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
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

    public function process(Message $message, MiddlewareHandler $handler): void
    {
        $this->log->info('Processing kafka message: '.$message->getPayload(), [
            'original' => (array) $message->getOriginal(),
        ]);

        $handler->handle($message);
    }
}
