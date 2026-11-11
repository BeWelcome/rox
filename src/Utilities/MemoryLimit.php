<?php

namespace App\Utilities;

/**
 * Raises PHP's memory limit for a task that needs more, and restores it afterwards. A higher limit, or none, stays:
 * the task runs with it.
 */
final class MemoryLimit
{
    /**
     * Runs the task with at least the given memory limit (php.ini notation: 512M, 1G).
     *
     * @template T
     *
     * @param callable(): T $task
     *
     * @return T
     */
    public static function atLeast(string $limit, callable $task): mixed
    {
        $current = (string) \ini_get('memory_limit');
        $raise = '-1' !== $current && self::bytes($current) < self::bytes($limit);
        if ($raise) {
            ini_set('memory_limit', $limit);
        }

        try {
            return $task();
        } finally {
            if ($raise) {
                ini_set('memory_limit', $current);
            }
        }
    }

    /**
     * A memory size of php.ini (512M, 1G, 1048576) in bytes.
     */
    public static function bytes(string $size): int
    {
        $value = (int) $size;

        return match (strtoupper(substr(trim($size), -1))) {
            'G' => $value * 1024 ** 3,
            'M' => $value * 1024 ** 2,
            'K' => $value * 1024,
            default => $value,
        };
    }
}
