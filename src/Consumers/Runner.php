<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Connectors\Consumer\Manager;

class Runner
{
    /**
     * @var Manager
     */
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function run(int $times = null): void
    {
        if ($times) {
            for ($i = 0; $i < $times; $i++) {
                $this->manager->handleMessage();
            }

            return;
        }

        while (true) {
            $this->manager->handleMessage();;
        }
    }
}
