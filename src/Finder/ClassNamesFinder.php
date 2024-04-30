<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan\Finder;

use TomasVotruba\PHPStanBodyscan\ClassNameResolver;
use TomasVotruba\PHPStanBodyscan\ValueObject\ClassNames;
use TomasVotruba\PHPStanBodyscan\ValueObject\FileWithClass;

final readonly class ClassNamesFinder
{
    public function __construct(
        private ClassNameResolver $classNameResolver,
    ) {
    }

    /**
     * @param string[] $filePaths
     * @return FileWithClass[]
     */
    public function resolveClassNamesToCheck(array $filePaths): array
    {
        $filesWithClasses = [];

        foreach ($filePaths as $filePath) {
            $classNames = $this->classNameResolver->resolveFromFromFilePath($filePath);
            if (! $classNames instanceof ClassNames) {
                continue;
            }

            $filesWithClasses[] = new FileWithClass(
                $filePath,
                $classNames->getClassName(),
                $classNames->hasParentClassOrInterface(),
                $classNames->getAttributes(),
            );
        }

        return $filesWithClasses;
    }
}
