<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator;

use AndreyTech\PHPUnit\Cobertura\Comparator\Parser\File;
use AndreyTech\PHPUnit\Cobertura\Comparator\Parser\Metrics;
use Exception;
use Generator;
use SimpleXMLElement;

final class Parser
{
    private int $timestamp = 0;

    public function __construct(
        private readonly File $file,
        private readonly bool $ignoreBranchRate
    ) {
    }

    /**
     * @return Generator<Metrics>
     *
     * @throws Exception
     */
    public function parse(): Generator
    {
        $xml = new SimpleXMLElement(
            $this->file->getContent()
        );

        $this->timestamp = isset($xml['timestamp']) ? (int) $xml['timestamp'] : 0;

        foreach ($xml->xpath('//class') ?? [] as $class) {
            foreach ($class->methods->method ?? [] as $method) {
                yield $this->buildMetrics($class, $method);
            }
        }
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    private function buildMetrics(
        SimpleXMLElement $class,
        SimpleXMLElement $method
    ): Metrics {
        return new Metrics(
            file: (string) $class['filename'],
            className: (string) $class['name'],
            classLineRate: (float) $class['line-rate'],
            classBranchRate: $this->ignoreBranchRate ? 0.0 : (float) $class['branch-rate'],
            methodName: (string) $method['name'],
            methodLineRate: (float) $method['line-rate'],
            methodBranchRate: $this->ignoreBranchRate ? 0.0 : (float) $method['branch-rate']
        );
    }
}
