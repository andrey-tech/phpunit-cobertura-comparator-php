<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace Test\Functional;

use Symfony\Component\Process\Process;

final class ScriptTest extends TestCase
{
    private const string SCRIPT_FILE = __DIR__ . '/../../bin/phpunit-cobertura-comparator';

    public function testError(): void
    {
        $process = $this->runProcess([
            '--no-color',
            'xxx.xml',
            'yyy.xml',
        ]);

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString(
            'ERROR: Cannot find readable Cobertura XML file "xxx.xml"',
            $process->getOutput()
        );
    }

    public function testNoRegressions(): void
    {
        $process = $this->runProcess([
            '--no-color',
            $this->getDataFileAbsolutePath('01/cobertura-old.xml'),
            $this->getDataFileAbsolutePath('01/cobertura-new.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('01/result.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(0, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    public function testLineRateRegressions(): void
    {
        $process = $this->runProcess([
            '--no-color',
            $this->getDataFileAbsolutePath('02/cobertura-old.xml'),
            $this->getDataFileAbsolutePath('02/cobertura-new.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('02/result.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(2, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    public function testLineRateRegressionsIgnoreBranchRate(): void
    {
        $process = $this->runProcess([
            '--no-color',
            '--ignore-branch-rate',
            $this->getDataFileAbsolutePath('02/cobertura-old.xml'),
            $this->getDataFileAbsolutePath('02/cobertura-new.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('02/result-ignore-branch-rate.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(2, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    public function testBranchRateRegressionsIgnoreBranchRate(): void
    {
        $process = $this->runProcess([
            '--no-color',
            '--ignore-branch-rate',
            $this->getDataFileAbsolutePath('03/cobertura-old.xml'),
            $this->getDataFileAbsolutePath('03/cobertura-new.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('03/result-ignore-branch-rate.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(0, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    public function testBranchRateRegressions(): void
    {
        $process = $this->runProcess([
            '--no-color',
            $this->getDataFileAbsolutePath('03/cobertura-old.xml'),
            $this->getDataFileAbsolutePath('03/cobertura-new.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('03/result.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(2, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    public function testNewAndDelLineRateRegressions(): void
    {
        $process = $this->runProcess([
            '--no-color',
            $this->getDataFileAbsolutePath('04/cobertura-old.xml'),
            $this->getDataFileAbsolutePath('04/cobertura-new.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('04/result.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(2, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    /**
     * @param list<string> $options
     */
    private function runProcess(array $options = []): Process
    {
        $process = new Process([
            'php',
            self::SCRIPT_FILE,
            ...$options
        ]);

        $process->setTimeout(5);
        $process->run();

        return $process;
    }
}
