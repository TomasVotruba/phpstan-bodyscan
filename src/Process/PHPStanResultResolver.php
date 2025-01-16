<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Process;

use PHPStanBodyscan202501\Symfony\Component\Process\Process;
use TomasVotruba\PHPStanBodyscan\Exception\AnalysisFailedException;
use TomasVotruba\PHPStanBodyscan\Logger;
use TomasVotruba\PHPStanBodyscan\Utils\JsonLoader;
final class PHPStanResultResolver
{
    /**
     * @return array<string, mixed[]>
     */
    public function resolve(Process $process) : array
    {
        $jsonResult = $process->getOutput();
        $json = JsonLoader::loadToArray($jsonResult, $process);
        // fatal errors, they stop the analyss
        if ((int) $json['totals']['errors'] > 0) {
            $this->failForFatalErrors($jsonResult, $process, (int) $json['totals']['errors']);
        }
        return $json;
    }
    /**
     * @return never
     */
    private function failForFatalErrors(string $jsonResult, Process $analyseLevelProcess, int $fatalErrorCount)
    {
        $loggedOutput = $jsonResult ?: $analyseLevelProcess->getErrorOutput();
        Logger::log($loggedOutput);
        throw new AnalysisFailedException(\sprintf('PHPStan failed with %d fatal errors. See %s for more', $fatalErrorCount, Logger::LOG_FILE_PATH));
    }
}
