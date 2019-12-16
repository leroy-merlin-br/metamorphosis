<?php
namespace Metamorphosis;

class Manager
{
    /**
     * @var array
     */
    private static $setting = [];

    /**
     * @var array
     */
    private static $middlewares = [];

    /**
     * @return mixed
     */
    public static function get(string $key = null)
    {
        if (!$key) {
            return self::$setting;
        }

        return array_get(self::$setting, $key);
    }

    public static function set(array $config): void
    {
        $middlewares = $config['middlewares'] ?? [];
        unset($config['middlewares']);

        self::$setting = $config;

        foreach ($middlewares as $middleware) {
            self::$middlewares[] = is_string($middleware) ? app($middleware) : $middleware;
        }
    }

    public static function middlewares(): array
    {
        return self::$middlewares;
    }
}
