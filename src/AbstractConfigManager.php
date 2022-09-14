<?php

namespace Metamorphosis;

use Illuminate\Support\Arr;

abstract class AbstractConfigManager
{
    /**
     * @var mixed[]
     */
    protected array $setting = [];

    /**
     * @var mixed[]
     */
    protected array $middlewares = [];

    abstract public function set(array $config, ?array $commandConfig = null): void;

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(?string $key = null, $default = null)
    {
        if (!$key) {
            return $this->setting;
        }

        return Arr::get($this->setting, $key, $default);
    }

    public function has(string $key): bool
    {
        return !is_null($this->get($key));
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }

    protected function remove(string $key): void
    {
        unset($this->setting[$key]);
    }
}
