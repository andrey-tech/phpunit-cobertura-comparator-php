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
    /**
     * @return Generator<Metrics>
     *
     * @throws Exception
     */
    public function parse(File $file): Generator
    {
        $xml = new SimpleXMLElement(
            $file->getContent()
        );

        foreach ($xml->xpath('//class') ?? [] as $class) {
            foreach ($class->methods->method ?? [] as $method) {
                yield new Metrics(
                    file: (string) $class['filename'],
                    className: (string) $class['name'],
                    classLineRate: (float) $class['line-rate'],
                    classBranchRate: (float) $class['branch-rate'],
                    methodName: (string) $method['name'],
                    methodLineRate: (float) $method['line-rate'],
                    methodBranchRate: (float) $method['branch-rate']
                );
            }
        }
    }
}
