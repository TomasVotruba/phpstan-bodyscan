<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Utils;

use JsonException;

final class JsonLoader
{
    public static function loadToArray(string $json): array
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new JsonException(sprintf(
                'Could not decode JSON from phpstan: "%s"',
                $json ?: $analyseLevelProcess->getErrorOutput()
            ), 0, $jsonException);
        }
    }
}
