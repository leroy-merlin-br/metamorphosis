<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Throwable;

abstract class AbstractHandler implements Handler
{
    /**
     * Merge and override config from kafka file.
     *
     * @var ConsumerConfigOptions
     */
    private $configOptions;

    public function __construct(ConsumerConfigOptions $configOptions = null)
    {
        $this->configOptions = $configOptions;
    }

    public function warning(ResponseWarningException $exception): void
    {
    }

    public function failed(Throwable $throwable): void
    {
    }

    public function finished(): void
    {
    }

    public function getConfigOptions(): ?ConsumerConfigOptions
    {
        return $this->configOptions;
    }
}
