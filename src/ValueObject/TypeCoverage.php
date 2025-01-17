<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\ValueObject;

final class TypeCoverage
{
    /**
     * @readonly
     * @var string
     */
    private $category;
    /**
     * @readonly
     * @var float
     */
    private $relative;
    /**
     * @readonly
     * @var int
     */
    private $totalCount;
    public function __construct(string $category, float $relative, int $totalCount)
    {
        $this->category = $category;
        $this->relative = $relative;
        $this->totalCount = $totalCount;
    }
    public function getCategory() : string
    {
        return $this->category;
    }
    public function getRelative() : float
    {
        return $this->relative;
    }
    public function getTotalCount() : int
    {
        return $this->totalCount;
    }
}
