<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

final class Configurator
{
    public ArgvInput $input;

    public function __construct()
    {
        $this->input = new ArgvInput();
    }

    public function configure(): void
    {
        $definition = new InputDefinition();

        $definition->addArguments([
            new InputArgument('cobertura-old-file', InputArgument::REQUIRED, 'Path to old Cobertura XML file.'),
            new InputArgument('cobertura-new-file', InputArgument::REQUIRED, 'Path to new Cobertura XML file.'),
        ]);

        $definition->addOptions([
            new InputOption('no-color', null, InputOption::VALUE_NONE, 'Disable color output.'),
            new InputOption('ignore-branch-coverage', null, InputOption::VALUE_NONE, 'Ignore branch coverage.'),
        ]);

        $this->input = new ArgvInput(null, $definition);
    }

    public function getCoberturaOldFile(): string
    {
        return (string) $this->input->getArgument('cobertura-old-file');
    }

    public function getCoberturaNewFile(): string
    {
        return (string) $this->input->getArgument('cobertura-new-file');
    }

    public function isNoColor(): bool
    {
        return (bool) $this->input->getOption('no-color');
    }

    public function isIgnoreBranchCoverage(): bool
    {
        return (bool) $this->input->getOption('ignore-branch-coverage');
    }
}
