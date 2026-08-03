<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/bin', __DIR__ . '/src', __DIR__ . '/tests'])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        earlyReturn: true
    )
    ->withPhpSets()
    ->withRootFiles()
    ->withImportNames(removeUnusedImports: true)
    ->withSkip(['*/scoper.php', '*/Source/*', '*/Fixture/*']);
