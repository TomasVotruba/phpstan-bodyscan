<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Utils;

final class ComposerLoader
{
    public static function getBinDirectory(string $projectDirectory): ?string
    {
        $content = file_get_contents($projectDirectory . '/composer.json');
        if (is_string($content)) {
            $content = json_decode($content, true);
            return $content['config']['bin-dir'] ?? null;
        }

        return null;
    }
}
