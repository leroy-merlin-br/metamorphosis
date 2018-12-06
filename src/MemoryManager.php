<?php
namespace Metamorphosis;

class MemoryManager
{
    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param int $memoryLimit in megabytes
     */
    public function memoryExceeded(int $memoryLimit = null): bool
    {
        return $memoryLimit && (memory_get_usage(true) / 1024 / 1024) >= $memoryLimit;
    }
}
