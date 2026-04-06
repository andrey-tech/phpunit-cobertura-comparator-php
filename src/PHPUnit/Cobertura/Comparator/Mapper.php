<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator;

use AndreyTech\PHPUnit\Cobertura\Comparator\Mapper\ClassRegression;
use AndreyTech\PHPUnit\Cobertura\Comparator\Mapper\MethodRegression;
use stdClass;

use function array_values;

final class Mapper
{
    /**
     * @param iterable<stdClass> $rows
     *
     * @return list<ClassRegression>
     */
    public function map(iterable $rows): array
    {
        $classes = [];

        foreach ($rows as $row) {
            $name = (string) $row->class_name;

            $classes[$name] ??= new ClassRegression(
                file: (string) $row->file,
                name: $name,
                status: (string) $row->class_status,
                oldLineRate: (float) $row->old_class_line_rate,
                newLineRate: (float) $row->new_class_line_rate,
                oldBranchRate: (float) $row->old_class_branch_rate,
                newBranchRate: (float) $row->new_class_branch_rate
            );

            $classes[$name]->addMethod(
                new MethodRegression(
                    name: (string) $row->method_name,
                    status: (string) $row->method_status,
                    oldLineRate: $this->toFloatOrNull($row->old_method_line_rate),
                    newLineRate: $this->toFloatOrNull($row->new_method_line_rate),
                    oldBranchRate: $this->toFloatOrNull($row->old_method_branch_rate),
                    newBranchRate: $this->toFloatOrNull($row->new_method_branch_rate)
                )
            );
        }

        return array_values($classes);
    }

    private function toFloatOrNull(mixed $value): ?float
    {
        return (null === $value) ? null : (float) $value;
    }
}
