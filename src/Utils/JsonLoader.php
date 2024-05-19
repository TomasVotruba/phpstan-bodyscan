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
            $response = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($response)) {
                throw new JsonException(sprintf(
                    'Expected JSON to be an array, instead we got: "%s"',
                    $json
                ));
            }

            return $response;
        } catch (JsonException $jsonException) {
            throw new JsonException(sprintf(
<<<<<<< HEAD
                'Could not decode JSON from phpstan: "%s"',
<<<<<<< HEAD
                $json ?: $process->getErrorOutput()
=======
                'Could not decode JSON from PHPStan: "%s"',
                $json ?: $process->getErrorOutput() ?: $process->getOutput()
>>>>>>> 728d076 (fixup! fixup! misc)
=======
                $json ?: $process->getErrorOutput() ?: $process->getOutput()
>>>>>>> bc65b82 (misc)
            ), 0, $jsonException);
        }
    }
}
