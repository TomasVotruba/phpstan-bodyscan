<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan;

final class Logger
{
    /**
     * @var string
     */
    public const LOG_FILE_PATH = 'bodyscan-log.txt';
    public static function log(string $message) : void
    {
        \file_put_contents(self::LOG_FILE_PATH, $message . \PHP_EOL . \PHP_EOL, \FILE_APPEND);
    }
}
