<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\TopicHandler\ConfigOptions;

abstract class AbstractHandler implements Handler
{
    /**
     * Merge and override config from kafka file.
     *
     * @var ConfigOptions
     */
    private $configOptions;

    public function __construct(ConfigOptions $configOptions = null)
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

    public function getConfigOptions(): ?ConfigOptions
    {
        return $this->configOptions;
    }
}
