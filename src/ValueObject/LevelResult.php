<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final class LevelResult
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
    /**
     * @var int|null
     */
    private $changeToPreviousLevel;
    public function __construct(int $level, int $errorCount)
    {
        $this->level = $level;
        $this->errorCount = $errorCount;
    }
    public function setChangeToPreviousLevel(int $changeToPreviousLevel) : void
    {
        $this->changeToPreviousLevel = $changeToPreviousLevel;
    }
    public function getChangeToPreviousLevel() : ?int
    {
        return $this->changeToPreviousLevel;
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
