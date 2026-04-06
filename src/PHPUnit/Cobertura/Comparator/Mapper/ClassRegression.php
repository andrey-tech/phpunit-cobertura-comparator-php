<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator\Mapper;

final class ClassRegression
{
    /**
     * @var list<MethodRegression>
     */
    private array $methods = [];

    public function __construct(
        public readonly string $file,
        public readonly string $name,
        public readonly string $status,
        public readonly float $oldLineRate,
        public readonly float $newLineRate,
        public readonly float $oldBranchRate,
        public readonly float $newBranchRate,
    ) {
    }

    public function addMethod(MethodRegression $methodRegression): void
    {
        $this->methods[] = $methodRegression;
    }

    /**
     * @return list<MethodRegression>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
