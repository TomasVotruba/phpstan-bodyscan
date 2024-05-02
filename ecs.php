<?php

declare(strict_types=1);
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withRules([
        LineLengthFixer::class,
    ])
    ->withPreparedSets(psr12: true, common: true, symplify: true);
