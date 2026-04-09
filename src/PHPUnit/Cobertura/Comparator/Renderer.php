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
use AndreyTech\PHPUnit\Cobertura\Comparator\Renderer\Colorizer;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

final readonly class Renderer
{
    public function __construct(
        private OutputInterface $output,
        private Colorizer $colorizer,
        private bool $ignoreBranchRate
    ) {
    }

    /**
     * @param iterable<ClassRegression> $classRegressionList
     */
    public function render(iterable $classRegressionList): void
    {
        foreach ($classRegressionList as $classRegression) {
            $this->renderTable($classRegression);
        }
    }

    private function renderTable(ClassRegression $classRegression): void
    {
        $this->renderTableTitle($classRegression);

        $table = $this->buildTable();
        $table = $this->addTableHeader($table);
        $this->addTableClassRow($table, $classRegression);

        foreach ($classRegression->getMethods() as $methodRegression) {
            $this->addTableMethodRow($table, $methodRegression);
        }

        $table->render();
        $this->addTableSeparator();
    }

    private function renderTableTitle(ClassRegression $classRegression): void
    {
        $this->output->writeln(
            sprintf('<fg=yellow>CLASS: %s</>', $classRegression->name)
        );
    }

    private function buildTable(): Table
    {
        $style = new TableStyle();
        $style->setCellHeaderFormat('<fg=white;bg=default;options=bold>%s</>');

        $table = new Table($this->output);
        $table->setStyle($style);

        return $table;
    }

    private function addTableHeader(Table $table): Table
    {
        $table->setHeaders([
            'Method',
            'Status',
            'Line coverage, %',
            ...($this->ignoreBranchRate ? [] : ['Branch coverage, %']),
        ]);

        $table->setColumnWidths([25]);
        $table->setColumnMaxWidth(0, 50);

        return $table;
    }

    private function addTableClassRow(Table $table, ClassRegression $classRegression): void
    {
        $branchRate = $this->colorizer->colorizeCoverage(
            $classRegression->oldBranchRate,
            $classRegression->newBranchRate
        );

        $table->addRow([
            '<fg=white;bg=default;options=bold>CLASS</>',
            $this->colorizer->colorizeStatus($classRegression->status),
            $this->colorizer->colorizeCoverage($classRegression->oldLineRate, $classRegression->newLineRate),
            ...($this->ignoreBranchRate ? [] : [$branchRate]),
        ]);
    }

    private function addTableMethodRow(Table $table, MethodRegression $methodRegression): void
    {
        $branchRate = $this->colorizer->colorizeCoverage(
            $methodRegression->oldBranchRate,
            $methodRegression->newBranchRate
        );

        $table->addRow([
            $methodRegression->name,
            $this->colorizer->colorizeStatus($methodRegression->status),
            $this->colorizer->colorizeCoverage($methodRegression->oldLineRate, $methodRegression->newLineRate),
            ...($this->ignoreBranchRate ? [] : [$branchRate]),
        ]);
    }

    private function addTableSeparator(): void
    {
        $this->output->writeln('');
    }
}
