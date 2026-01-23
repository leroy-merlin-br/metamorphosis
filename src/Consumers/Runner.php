<?php

namespace Metamorphosis\Consumers;

use Metamorphosis\Connectors\Consumer\Manager;

class Runner
{
    private Manager $manager;

    private bool $shuttingDown = false;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function run(?int $times = null): void
    {
        if ($times) {
            for ($i = 0; $i < $times; $i++) {
                $this->manager->handleMessage();
                if ($this->shuttingDown) {
                    return;
                }
            }

            return;
        }

        while (true) {
            $this->manager->handleMessage();
            if ($this->shuttingDown) {
                return;
            }
        }
    }

    public function shutdown(): void
    {
        $this->shuttingDown = true;
    }
}
