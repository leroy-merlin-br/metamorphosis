<?php
namespace Metamorphosis;

class Manager
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
     * @return mixed
     */
    public function get(string $key = null)
    {
        if (!$key) {
            return $this->setting;
        }

        return array_get($this->setting, $key);
    }

    public function set(array $config): void
    {
        $middlewares = $config['middlewares'] ?? [];
        unset($config['middlewares']);

        $this->setting = $config;

        foreach ($middlewares as $middleware) {
            $this->middlewares[] = is_string($middleware) ? app($middleware) : $middleware;
        }
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }
}
