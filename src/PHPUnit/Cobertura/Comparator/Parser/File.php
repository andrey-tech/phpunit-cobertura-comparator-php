<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator\Parser;

use RuntimeException;

use function file_get_contents;
use function is_file;
use function is_readable;
use function sprintf;

final readonly class File
{
    public function __construct(
        private string $file
    ) {
    }

    public function getContent(): string
    {
        if (!is_file($this->file) || !is_readable($this->file)) {
            throw new RuntimeException(
                sprintf('Cannot find readable Cobertura XML file "%s".', $this->file)
            );
        }

        $content = file_get_contents($this->file);

        if (false === $content) {
            throw new RuntimeException(
                sprintf('Failed get content of Cobertura XML file "%s".', $this->file)
            );
        }

        return $content;
    }
}
