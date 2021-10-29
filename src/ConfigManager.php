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
        $consumerHandler = null;
        if ($handlerName = $config['handler'] ?? null) {
            // Add Consumer Handler as a middleware
            $consumerHandler = app($handlerName);
        }
        $this->setConfig($config, $consumerHandler);

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

    private function remove(string $key): void
    {
        unset($this->setting[$key]);
    }

    public function has(string $key): bool
    {
        return !is_null($this->get($key));
    }

    public function middlewares(): array
    {
        return $this->middlewares;
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
