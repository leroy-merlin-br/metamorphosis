<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Config;
use Metamorphosis\Message;

class Dispatcher
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function handle(Message $message): void
    {
        if ($message->hasError()) {
            $this->invalidMessage($message);

            return;
        }

        $middlewares = $this->config->getMiddlewares();

        try {
            foreach ($middlewares as $middleware) {
                $message = app($middleware)->process($message);
            }

            $this->config->getConsumerGroupHandler()->handle($message);
        } catch (\Exception $exception) {
            $this->handleError($exception);
        }
    }

    protected function invalidMessage(Message $message): void
    {
        $exception = new \Exception();

        $this->handleError($exception);
    }

    protected function handleError(\Exception $exception)
    {
        $this->config->getConsumerGroupHandler()->failed($exception);
    }
}
