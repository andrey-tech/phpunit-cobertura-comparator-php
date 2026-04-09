# PHPUnit Cobertura Comparator

![PHPUnit Cobertura Comparator logo](./assets/phpunit-cobertura-comparator-logo.png)

[![Latest Stable Version](https://poser.pugx.org/andrey-tech/phpunit-cobertura-comparator-php/v)](https://packagist.org/packages/andrey-tech/phpunit-cobertura-comparator-php)
[![PHP workflow](https://github.com/andrey-tech/phpunit-cobertura-comparator-php/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/andrey-tech/phpunit-cobertura-comparator-php/actions/workflows/php.yml)
[![Total Downloads](https://poser.pugx.org/andrey-tech/phpunit-cobertura-comparator-php/downloads)](//packagist.org/packages/andrey-tech/phpunit-cobertura-comparator-php)
[![PHP Version Require](https://poser.pugx.org/andrey-tech/phpunit-cobertura-comparator-php/require/php)](https://packagist.org/packages/andrey-tech/phpunit-cobertura-comparator-php)
[![License](https://poser.pugx.org/andrey-tech/phpunit-cobertura-comparator-php/license)](https://packagist.org/packages/andrey-tech/phpunit-cobertura-comparator-php)

PHPUnit Cobertura Comparator is a lightweight CLI tool to track 
[PHPUnit](https://docs.phpunit.de/en/12.2/code-coverage.html#software-metrics-for-code-coverage)
code coverage regressions using
[Cobertura](https://github.com/cobertura/cobertura) reports.

## Installation

The tool requires [PHP](https://www.php.net) version 8.3 or higher.

Install via Composer:

```shell
composer require --dev andrey-tech/phpunit-cobertura-comparator-php
```

## Usage

The command line:

```shell
./vendor/bin/phpunit-cobertura-comparator <path to old Cobertura XML file> <path to new Cobertura XML file>
```

The command line interface also accepts the following optional arguments:

- `--no-colors` — disable ANSI color output;
- `--ignore-branch-rate` — ignore branch-rate in a Cobertura XML file.

An example command line:

```shell
./vendor/bin/phpunit-cobertura-comparator cobertura-old.xml cobertura-new.xml 
```

An example of the console output:

```text
Coverage comparison: cobertura-old.xml -> cobertura-new.xml
Coverage regressions found in 1 class(es):
CLASS: App\Purchase\Fixer
+---------------------------+--------+------------------+--------------------+
| Method                    | Status | Line coverage, % | Branch coverage, % |
+---------------------------+--------+------------------+--------------------+
| CLASS                     | old    | 98.44 -> 92.58   | 92.53 -> 85.22     |
| fix                       | old    | 100.00 -> 100.00 | 100.00 -> 100.00   |
| fixPrice                  | old    | 96.11 -> 100.00  | 94.76 -> 75.33     |
| fixRate                   | new    | x -> 85.90       | x -> 95.24         |
| fixCharges                | del    | 100.00 -> x      | 100.00 -> x        |
+---------------------------+--------+------------------+--------------------+

Exit code: 2. Time: 6 ms. Memory: 1.40/2.00 MiB.
```

Columns in the table:
- `Method` — the method name;
- `Status` — the status of the method or class (`old`, `new`, `del`);
- `Line coverage, %` — the line coverage of the method or class (`old` -> `new`);
- `Branch coverage, %` — the branch coverage of the method or class (`old` -> `new`).

The tool shows line and branch coverage, measured by PHP Unit, in ANSI colors:

| Color    | Line or branch coverage      | Status  |
|----------|------------------------------|---------|
| `red`    | The coverage has decreased   | —       |
| `yellow` | —                            | Deleted |
| `green`  | The coverage has increased   | New     |
| `white`  | The coverage has not changed | Old     |


## Exit codes

The tool defines different exit codes:

| Code | Description                                                             |
|------|-------------------------------------------------------------------------|
| 0    | Everything worked as expected and no coverage regressions found         |
| 1    | An error/exception occurred which has interrupted tool during execution |
| 2    | Everything worked as expected but coverage regressions found            |


## Authors and Maintainers

The author and maintainer of PHPUnit Cobertura Comparator is [andrey-tech](https://github.com/andrey-tech).

## License

This tool is licensed under the [MIT license](./LICENSE).
