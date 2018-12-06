<?php
namespace Tests;

use Metamorphosis\MemoryManager;

class MemoryManagerTest extends LaravelTestCase
{
    public function testMemoryShouldNotBeExceededIfNoLimitIsGiven(): void
    {
        // Set
        $manager = new MemoryManager();

        // Actions
        $result = $manager->memoryExceeded(null);

        // Assertions
        $this->assertFalse($result);
    }

    public function testMemoryShouldBeExceeded(): void
    {
        // Set
        $manager = new MemoryManager();

        // Actions
        $result = $manager->memoryExceeded(1);

        // Assertions
        $this->assertTrue($result);
    }
}
