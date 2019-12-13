<?php
namespace Metamorphosis;

class Manager
{
    /**
     * @var array
     */
    static private $setting = [];

    /**
     * @var array
     */
    static private $middlewares = [];

    /**
     * @return mixed
     */
    static function get(string $key)
    {
        return self::$setting[$key] ?? null;
    }

    static function set(array $config): void
    {
        $middlewares = $config['middlewares'];
        unset($config['middlewares']);

        self::$setting = $config;

        foreach ($middlewares as $middleware) {
            self::$middlewares[] = is_string($middleware) ? app($middleware) : $middleware;
        }
    }

    static function middlewares(): array
    {
        return self::$middlewares;
    }
}
