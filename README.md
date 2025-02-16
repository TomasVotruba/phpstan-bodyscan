# PHPStan Bodyscan

[![Downloads total](https://img.shields.io/packagist/dt/tomasvotruba/phpstan-bodyscan.svg?style=flat-square)](https://packagist.org/packages/tomasvotruba/phpstan-bodyscan/stats)

* Do you want to get quick glimpse of new project code quality?
* Do you want to know, what PHPStan level is the best for your project?
* Do you want to know, how much errors you're facing per level to see how hard it will be to reach them?

Get error count for each PHPStan level!

<br>

## How does it work?

First, we look into the project root for `phpstan.neon` file.

* If found, we reuse it.
* If not, we look for defaults source code paths like `/src`, `/app`, `/tests`, etc.

Then we run PHPStan for each level from 0 to 8. We count errors and display them in a table.

<br>

## Install

```bash
composer require tomasvotruba/phpstan-bodyscan --dev
```

## Usage

Run tool in your project. It will take some time, as it will run full PHPStan analysis for each level.


```bash
vendor/bin/phpstan-bodyscan
```

↓

To get errors count per level:

```bash
+-------+-------------+-----------+
| Level | Error count | Increment |
+-------+-------------+-----------+
|     0 |           0 |         - |
|     1 |          35 |      + 35 |
|     2 |          59 |      + 24 |
|     3 |          59 |         - |
|     4 |         120 |      + 61 |
|     5 |         120 |         - |
|     6 |         253 |     + 133 |
|     7 |         350 |      + 97 |
|     8 |         359 |       + 9 |
+-------+-------------+-----------;
```


<br>


## Do you want to run levels without extensions?

```bash
vendor/bin/phpstan-bodyscan --bare
```

<br>


## Do you need a JSON format?

We got you covered:

```bash
vendor/bin/phpstan-bodyscan --json
```

↓

```bash
[
    {
        "level": 0,
        "error_count": 0,
        "increment_count": 0
    },
    {
        "level": 1,
        "error_count": 5,
        "increment_count": 5
    },
    {
        "level": 2,
        "error_count": 25,
        "increment_count": 20
    }
]
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
