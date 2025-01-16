<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Utils;

use PHPStanBodyscan202501\Webmozart\Assert\Assert;
final class FileLoader
{
    /**
     * @return array<string, string>
     */
    public static function resolveEnvVariablesFromFile(string $envFile) : array
    {
        Assert::fileExists($envFile);
        // load env file
        /** @var string $envContent */
        $envContent = \file_get_contents($envFile);
        $envLines = \explode("\n", $envContent);
        // split by "="
        $envVariables = [];
        foreach ($envLines as $envLine) {
            $envLineParts = \explode('=', $envLine);
            if (\count($envLineParts) !== 2) {
                continue;
            }
            $envVariables[$envLineParts[0]] = $envLineParts[1];
        }
        return $envVariables;
    }
}
