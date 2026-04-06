<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator\Renderer;

use function array_shift;
use function explode;
use function implode;
use function sprintf;

final readonly class Colorizer
{
    private const string NO_VALUE = 'x';
    private const string DECOR_OK = 'green';
    private const string DECOR_ERROR = 'red+bold';
    private const string DECOR_WARNING = 'yellow';

    public function colorizeStatus(string $status): string
    {
        return match ($status) {
            'new' => $this->decorate('new', self::DECOR_OK),
            'del' => $this->decorate('new', self::DECOR_WARNING),
            default => $this->decorate($status),
        };
    }

    public function colorizeCoverage(?float $oldRate, ?float $newRate): string
    {
        return $this->decorate(
            sprintf('%s -> %s', $this->rateToPercent($oldRate), $this->rateToPercent($newRate)),
            $this->buildRateDecor($oldRate, $newRate)
        );
    }

    private function buildRateDecor(?float $oldRate, ?float $newRate): ?string
    {
        if (null === $oldRate || null === $newRate) {
            return null;
        }

        if ($newRate > $oldRate) {
            return self::DECOR_OK;
        }

        if ($newRate < $oldRate) {
            return self::DECOR_ERROR;
        }

        return null;
    }

    private function decorate(string $value, ?string $decor = null): string
    {
        if (null === $decor) {
            return $value;
        }

        $decorElements = explode('+', $decor);
        $tags = [sprintf('fg=%s', array_shift($decorElements))];

        $options = array_shift($decorElements);
        if (null !== $options) {
            $tags[] = sprintf('options=%s', $options);
        }

        return sprintf(
            '<%s>%s</>',
            implode(';', $tags),
            $value
        );
    }

    private function rateToPercent(?float $rate): string
    {
        if (null === $rate) {
            return self::NO_VALUE;
        }

        return sprintf('%.2f', 100.0 * $rate);
    }
}
