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
use AndreyTech\PHPUnit\Cobertura\Comparator\Parser\File;
use AndreyTech\PHPUnit\Cobertura\Comparator\Renderer\Colorizer;
use DateMalformedStringException;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

use function count;
use function sprintf;

final readonly class Application
{
    private const int EXIT_CODE_OK = 0;
    private const int EXIT_CODE_ERROR = 1;
    private const int EXIT_CODE_WARNING = 2;

    private ConsoleOutput $consoleOutput;
    private Configurator $configurator;
    private Stats $stats;

    public function __construct()
    {
        $this->consoleOutput = new ConsoleOutput();
        $this->configurator = new Configurator();
        $this->stats = new Stats();
    }

    public function run(): int
    {
        $this->stats->start();

        try {
            $exitCode = $this->doRun();
        } catch (Throwable $exception) {
            $exitCode = self::EXIT_CODE_ERROR;
            $this->printError(
                sprintf('ERROR: %s', $exception->getMessage())
            );
        }

        $this->stats->finish();
        $this->printStats($exitCode);

        return $exitCode;
    }

    /**
     * @throws Exception
     */
    private function doRun(): int
    {
        $this->configurator->configure();
        $this->consoleOutput->getFormatter()->setDecorated(!$this->configurator->isNoColor());

        $classRegressions = (new Mapper())->map(
            $this->parseCoberturaFiles()->getRegressions()
        );

        $this->printConclusion($classRegressions);

        (new Renderer(
            $this->consoleOutput,
            new Colorizer(),
            $this->configurator->isIgnoreBranchRate()
        ))->render($classRegressions);

        return 0 === count($classRegressions) ? self::EXIT_CODE_OK : self::EXIT_CODE_WARNING;
    }

    /**
     * @throws Exception
     */
    private function parseCoberturaFiles(): Storage
    {
        $storage = new Storage();

        $parser = $this->parseCoberturaFile($this->configurator->getCoberturaOldFile());
        $storage->store($parser->parse(), 0);
        $oldFileTimestamp = $parser->getTimestamp();

        $parser = $this->parseCoberturaFile($this->configurator->getCoberturaNewFile());
        $storage->store($parser->parse(), 1);

        $this->consoleOutput->writeln(
            sprintf(
                'Coverage comparison: %s -> %s',
                $this->formatTimestamp($oldFileTimestamp),
                $this->formatTimestamp($parser->getTimestamp())
            )
        );

        return $storage;
    }

    private function parseCoberturaFile(string $file): Parser
    {
        $this->consoleOutput->writeln(
            sprintf(
                "Parsing Cobertura XML file '%s'...",
                $file,
            )
        );

        return new Parser(
            new File($file),
            $this->configurator->isIgnoreBranchRate()
        );
    }

    /**
     * @param list<ClassRegression> $classRegressions
     */
    private function printConclusion(array $classRegressions): void
    {
        if (0 === count($classRegressions)) {
            $this->consoleOutput->writeln('<fg=green>No coverage regressions found</>');

            return;
        }

        $this->consoleOutput->writeln(
            sprintf('<fg=red>Coverage regressions found in %u class(es):</>', count($classRegressions))
        );
    }

    /**
     * @throws DateMalformedStringException
     */
    private function formatTimestamp(int $timestamp): string
    {
        return (new DateTimeImmutable('@' . $timestamp))->format(DateTimeImmutable::RFC3339);
    }

    private function printStats(int $exitCode): void
    {
        $this->consoleOutput->writeln(
            sprintf(
                'Exit code: %s. Time: %s. Memory: %s.',
                $exitCode,
                $this->stats->time(),
                $this->stats->memory()
            )
        );
    }

    private function printError(string $message): void
    {
        $this->consoleOutput->writeln(
            sprintf('<fg=red;options=bold>%s</>', $message)
        );
    }
}
