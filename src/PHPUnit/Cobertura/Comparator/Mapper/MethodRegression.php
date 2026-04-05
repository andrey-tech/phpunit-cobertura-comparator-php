<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator\Mapper;

final readonly class MethodRegression
{
    public function __construct(
        public string $name,
        public string $status,
        public ?float $oldLineRate,
        public ?float $newLineRate,
        public ?float $oldBranchRate,
        public ?float $newBranchRate
    ) {
    }
}