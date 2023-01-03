<?php

namespace Metamorphosis;

class ProducerConfigManager extends AbstractConfigManager
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function set(array $config, ?array $commandConfig = null): void
    {
        $this->setting = $config;

        $middlewares = $this->get('middlewares', []);
        $this->middlewares = [];
        $this->remove('middlewares');

        foreach ($middlewares as $middleware) {
            $this->middlewares[] = is_string($middleware)
                ? app(
                    $middleware,
                    ['configManager' => $this]
                )
                : $middleware;
        }
    }
}
