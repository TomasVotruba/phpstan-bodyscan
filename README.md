# PHPStan Bodyscan

[![Downloads total](https://img.shields.io/packagist/dt/tomasvotruba/phpstan-bodyscan.svg?style=flat-square)](https://packagist.org/packages/tomasvotruba/phpstan-bodyscan/stats)

Do you want to get quick glimpse of new project code quality?

Get error count for each PHPStan level!


## Install

```bash
composer require tomasvotruba/phpstan-bodyscan --dev
```

## Usage

Run tool in your project. It will take some time, as it will run PHPStan for each level.


```bash
vendor/bin/phpstan-bodyscan
```

↓

To get errors count per level:

```bash
+-------+-------------+
| Level | Error count |
+-------+-------------+
|     0 |           0 |
|     1 |          35 |
|     2 |          59 |
|     3 |          93 |
|     4 |         120 |
|     5 |         125 |
|     6 |         253 |
|     7 |         350 |
|     8 |         359 |
+-------+-------------+

<br>

Are you interested only in a few levels?

```bash
vendor/bin/phpstan-bodyscan run --max-level 3
```

<br>

Happy coding!
