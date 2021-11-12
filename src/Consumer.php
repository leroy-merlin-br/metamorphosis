<?php

namespace Metamorphosis;

use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Connectors\Consumer\Manager;
use Metamorphosis\TopicHandler\ConfigOptions;
use RdKafka\Message;

class Consumer
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var ConfigManager
     */
    private $configManager;

    public function __construct(ConfigManager $configManager, Manager $manager)
    {
        $this->manager = $manager;
        $this->configManager = $configManager;
    }

    public function setup(ConfigOptions $configOptions)
    {
        $this->configManager->set($configOptions->toArray());

        $this->manager = Factory::make($this->configManager);
    }

    public function consume(): ?Message
    {
        return $this->manager->consume();
    }
}
