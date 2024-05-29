<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Utils;

final class ComposerLoader
{
    /**
     * @var string
     */
    private const DEFAULT_VENDOR_BIN = 'vendor/bin/';
    public static function getPHPStanBinFile(string $projectDirectory) : string
    {
        $vendorBinDirectory = self::getBinDirectory($projectDirectory);
        if (\file_exists($vendorBinDirectory . '/phpstan')) {
            return $vendorBinDirectory . '/phpstan';
        }
        if (\file_exists($projectDirectory . '/vendor/bin/phpstan')) {
            return 'vendor/bin/phpstan';
        }
        // possible that /bin directory is used
        return 'bin/phpstan';
    }
    private static function getBinDirectory(string $projectDirectory) : string
    {
        $content = \file_get_contents($projectDirectory . '/composer.json');
        if (\is_string($content)) {
            $content = \json_decode($content, \true);
            return $content['config']['bin-dir'] ?? self::DEFAULT_VENDOR_BIN;
        }
        return self::DEFAULT_VENDOR_BIN;
    }
}
