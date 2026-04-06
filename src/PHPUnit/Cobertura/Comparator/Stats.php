<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator;

use function memory_get_peak_usage;
use function microtime;
use function round;
use function sprintf;

final class Stats
{
    /**
     * @var array<string, float>
     */
    private array $startTime = [];

    /**
     * @var array<string, float>
     */
    private array $finishTime = [];

    public function start(string $eventName = ''): void
    {
        $this->startTime[$eventName] = $this->getNowTime();
    }

    public function finish(string $eventName = ''): void
    {
        $this->finishTime[$eventName] = $this->getNowTime();
    }

    public function time(string $eventName = ''): string
    {
        $startTime = $this->startTime[$eventName] ?? 0.0;
        $finishTime = $this->finishTime[$eventName] ?? $this->getNowTime();

        return sprintf('%d ms', (int) round(1000.0 * ($finishTime - $startTime)));
    }

    public function memory(): string
    {
        return sprintf(
            '%.2f/%.2f MiB',
            $this->toMebibytes(memory_get_peak_usage()),
            $this->toMebibytes(memory_get_peak_usage(true))
        );
    }

    private function toMebibytes(int $bytes): float
    {
        return (float) $bytes / 1024.0 / 1024.0;
    }

    private function getNowTime(): float
    {
        return microtime(true);
    }
}
