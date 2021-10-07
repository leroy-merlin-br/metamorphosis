<?php
namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;

abstract class AbstractHandler implements Handler
{
    /**
     * Merge and override config from kafka file.
     *
     * @var array
     */
    private $configOptions;

    public function __construct(array $configOptions = [])
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

    public function getConfigOptions(): array
    {
        return $this->configOptions;
    }
}
