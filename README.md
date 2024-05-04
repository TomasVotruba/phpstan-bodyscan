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
```

<br>

### Limit level count

Are you interested only in a few levels? You can limit ranges by the options:

```bash
vendor/bin/phpstan-bodyscan run --min-level 0 --max-level 3
```

<br>

### Load env file

Some projects need to load `.env` file to run PHPStan. You can do it like this:

```bash
vendor/bin/phpstan-bodyscan run --env-file some-parameters.env
```

<br>

### Debugging

Running PHPStan on a new project you don't know might crash. To save data from finished levels, we dump them to the `bodyscan-log.txt` file.

If the run crashes for any reason, the PHPStan error output is also dumped to the same file.

<br>

Happy coding!
