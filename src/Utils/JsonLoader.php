<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Utils;

use JsonException;
use PHPStanBodyscan202501\Symfony\Component\Process\Process;
final class JsonLoader
{
    /**
     * @return array<string, mixed>
     */
    public static function loadToArray(string $json, Process $process) : array
    {
        try {
            $response = \json_decode($json, \true, 512, 0);
            if (!\is_array($response)) {
                throw new JsonException(\sprintf('Expected JSON to be an array, instead we got: "%s"', $json));
            }
            return $response;
        } catch (JsonException $jsonException) {
            throw new JsonException(\sprintf('Could not decode JSON from PHPStan: "%s"', ($json ?: $process->getErrorOutput()) ?: $process->getOutput()), 0, $jsonException);
        }
    }
}
