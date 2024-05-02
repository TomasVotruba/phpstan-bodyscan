<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Utils;

use JsonException;
use Symfony\Component\Process\Process;

final class JsonLoader
{
    /**
     * @return array<string, mixed>
     */
    public static function loadToArray(string $json, Process $process): array
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new JsonException(sprintf(
                'Could not decode JSON from phpstan: "%s"',
                $json ?: $process->getErrorOutput()
            ), 0, $jsonException);
        }
    }
}
