<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final class PHPStanLevelResult
{
    /**
     * @readonly
     * @var int
     */
    private $level;
    /**
     * @readonly
     * @var int
     */
    private $errorCount;
    public function __construct(int $level, int $errorCount)
    {
        $this->level = $level;
        $this->errorCount = $errorCount;
    }
    public function getLevel() : int
    {
        return $this->level;
    }
    public function getErrorCount() : int
    {
        return $this->errorCount;
    }
}
