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
use AndreyTech\PHPUnit\Cobertura\Comparator\Renderer\Colorizer;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

use function sprintf;

final class Application
{
    private const int EXIT_CODE_OK = 0;
    private const int EXIT_CODE_ERROR = 1;

    private ConsoleOutput $consoleOutput;
    private Stats $stats;

    public function __construct()
    {
        $this->consoleOutput = new ConsoleOutput();
        $this->stats = new Stats();
    }

    public function run(): int
    {
        $this->stats->start();

        try {
            $exitCode = $this->doRun();
        } catch (Throwable $exception) {
            $exitCode = self::EXIT_CODE_ERROR;
            $this->error(
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
        $parser = new Parser();
        $storage = new Storage();

        $storage->store(
            $parser->parse(
                new File('cobertura.xml')
            ),
            0
        );

        $storage->store(
            $parser->parse(
                new File('cobertura1.xml')
            ),
            1
        );

        $regressions = (new Mapper())->map($storage->getRegressions());

        $renderer = new Renderer(
            $this->consoleOutput,
            new Colorizer()
        );
        $renderer->render($regressions);

        return self::EXIT_CODE_OK;
    }

    private function printStats(int $exitCode): void
    {
        $this->consoleOutput->writeln(
            sprintf(
                'Exit code: %s, Time: %s, Memory: %s.',
                $exitCode,
                $this->stats->time(),
                $this->stats->memory()
            )
        );
    }

    private function error(string $message): void
    {
        $this->consoleOutput->writeln(
            sprintf('<fg=red;options=bold>%s</>', $message)
        );
    }
}
