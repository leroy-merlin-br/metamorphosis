<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\TopicHandler\BaseConfigOptions;

abstract class AbstractHandler implements Handler
{
    /**
     * Merge and override config from kafka file.
     *
     * @var BaseConfigOptions
     */
    private $configOptions;

    public function __construct(BaseConfigOptions $configOptions = null)
    {
        $this->configOptions = $configOptions;
    }

    public function warning(ResponseWarningException $exception): void
    {
    }

    public function failed(Exception $exception): void
    {
    }

    public function finished(): void
    {
    }

    public function getConfigOptions(): ?BaseConfigOptions
    {
        return $this->configOptions;
    }
}
