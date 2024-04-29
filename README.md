# Class Leak

[![Downloads total](https://img.shields.io/packagist/dt/tomasvotruba/class-leak.svg?style=flat-square)](https://packagist.org/packages/tomasvotruba/class-leak/stats)

Find leaking classes that you never use... and get rid of them.

## Install

```bash
composer require tomasvotruba/class-leak --dev
```

## Usage

Pass directories you want to check:

```bash
vendor/bin/class-leak check bin src
```

Make sure to exclude `/tests` directories, to keep reporting classes that are used in tests, but never used in the code-base.

<br>

Many types are excluded by default, as they're collected by framework magic, e.g. console command classes. To exclude another class, e.g. your interface collector, use `--skip-type`:

```bash
vendor/bin/class-leak check bin src --skip-type="App\\Contract\\SomeInterface"
```

What if your classes do no implement any type? Use `--skip-suffix` instead:

```bash
vendor/bin/class-leak check bin src --skip-suffix "Controller"
```

If you want to skip classes that use a specific attribute or have methods that use a specific attribute, use `--skip-attribute`:

```bash
vendor/bin/class-leak check bin src --skip-attribute "Symfony\\Component\\HttpKernel\\Attribute\\AsController"
```

<br>

Happy coding!
