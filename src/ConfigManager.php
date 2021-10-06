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
        $this->middlewares = [];
        $middlewares = $config['middlewares'] ?? [];
        unset($config['middlewares']);

        $this->setting = $config;
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = is_string($middleware) ? app($middleware, ['configManager' => $this]) : $middleware;
        }

        if ($handlerName = $config['handler'] ?? null) {
            // Add Consumer Handler as a middleware
            $consumerHandler = app($handlerName);

            $this->overrideHandlerConfig($consumerHandler);
            $this->middlewares[] = new ConsumerMiddleware($consumerHandler);
        }
    }

    public function replace(array $overrideConfig): void
    {
        $this->setting = array_replace_recursive($this->setting, $overrideConfig);
    }

    public function has(string $key): bool
    {
        return !is_null($this->get($key));
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }

    private function overrideHandlerConfig(AbstractHandler $handler): void
    {
        if (!$overrideConfig = $handler->getConfigOptions()) {
            return;
        }

        $this->replace($overrideConfig);
    }
}
