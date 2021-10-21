<?php
namespace Metamorphosis;

use Illuminate\Support\Arr;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class ConfigManager
{
    /**
     * @var array
     */
    private $setting = [];

    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key = null, $default = null)
    {
        if (!$key) {
            return $this->setting;
        }

        return Arr::get($this->setting, $key, $default);
    }

    public function set(array $config): void
    {
        if ($handlerName = $config['handler'] ?? null) {
            // Add Consumer Handler as a middleware
            $consumerHandler = app($handlerName);

            $this->setConfig($consumerHandler, $config);
        }

        foreach ($this->get('middlewares') as $middleware) {
            $this->middlewares[] = is_string($middleware) ? app($middleware, ['configManager' => $this]) : $middleware;
        }
        $this->middlewares[] = new ConsumerMiddleware($consumerHandler);
    }

    public function has(string $key): bool
    {
        return !is_null($this->get($key));
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }

    private function setConfig(AbstractHandler $handler, array $config): void
    {
        if (!$overrideConfig = $handler->getConfigOptions()) {
            $this->setting = $config;

            return;
        }

        $this->setting = $overrideConfig->toArray();
    }
}
