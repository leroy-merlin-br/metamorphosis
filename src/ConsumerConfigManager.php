<?php
namespace Metamorphosis;

use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class ConsumerConfigManager extends AbstractConfigManager
{
    public function set(array $config, ?array $commandConfig = null): void
    {
        $consumerHandler = null;
        if ($handlerName = $config['handler'] ?? null) {
            // Add Consumer Handler as a middleware
            $consumerHandler = app($handlerName);
        }
        $this->setConfig($config, $consumerHandler);
        $this->setCommandConfig($commandConfig);

        $middlewares = $this->get('middlewares', []);
        $this->middlewares = [];
        $this->remove('middlewares');

        foreach ($middlewares as $middleware) {
            $this->middlewares[] = is_string($middleware) ? app($middleware, ['configManager' => $this]) : $middleware;
        }

        if (!$consumerHandler) {
            return;
        }

        $this->middlewares[] = new ConsumerMiddleware($consumerHandler);
    }

    private function setCommandConfig(?array $commandConfig): void
    {
        if (!$commandConfig) {
            return;
        }

        $this->setting = array_merge($this->setting, $commandConfig);
    }

    private function setConfig(array $config, ?AbstractHandler $handler): void
    {
        if (!$handler || !$overrideConfig = $handler->getConfigOptions()) {
            $this->setting = $config;

            return;
        }

        $this->setting = $overrideConfig->toArray();
    }
}
