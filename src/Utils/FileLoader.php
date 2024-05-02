<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Utils;

use TomasVotruba\PHPStanBodyscan\Exception\ShouldNotHappenException;

final class FileLoader
{
    /**
     * @return array<string, string>
     */
    public static function resolveEnvVariablesFromFile(string $envFile): array
    {
        if (! file_exists($envFile)) {
            throw new ShouldNotHappenException(sprintf('Env file "%s" was not found.', $envFile));
        }

        // load env file
        /** @var string $envContent */
        $envContent = file_get_contents($envFile);

        $envLines = explode("\n", $envContent);

        // split by "="
        $envVariables = [];
        foreach ($envLines as $envLine) {
            $envLineParts = explode('=', $envLine);
            if (count($envLineParts) !== 2) {
                continue;
            }

            $envVariables[$envLineParts[0]] = $envLineParts[1];
        }

        return $envVariables;
    }
}
