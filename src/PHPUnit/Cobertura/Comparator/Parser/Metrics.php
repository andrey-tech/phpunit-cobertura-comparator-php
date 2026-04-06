<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator\Parser;

final readonly class Metrics
{
    public function __construct(
        public string $file,
        public string $className,
        public float $classLineRate,
        public float $classBranchRate,
        public string $methodName,
        public float $methodLineRate,
        public float $methodBranchRate,
    ) {
    }
}
