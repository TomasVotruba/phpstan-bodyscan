<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use TomasVotruba\PHPStanBodyscan\NodeDecorator\FullyQualifiedNameNodeDecorator;
use TomasVotruba\PHPStanBodyscan\NodeVisitor\ClassNameNodeVisitor;
use TomasVotruba\PHPStanBodyscan\ValueObject\ClassNames;

/**
 * @see \TomasVotruba\PHPStanBodyscan\Tests\ClassNameResolver\ClassNameResolverTest
 */
final readonly class ClassNameResolver
{
    public function __construct(
        private Parser $parser,
        private FullyQualifiedNameNodeDecorator $fullyQualifiedNameNodeDecorator
    ) {
    }

    public function resolveFromFromFilePath(string $filePath): ?ClassNames
    {
        /** @var string $fileContents */
        $fileContents = file_get_contents($filePath);

        $stmts = $this->parser->parse($fileContents);
        if ($stmts === null) {
            return null;
        }

        $this->fullyQualifiedNameNodeDecorator->decorate($stmts);

        $classNameNodeVisitor = new ClassNameNodeVisitor();
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($classNameNodeVisitor);
        $nodeTraverser->traverse($stmts);

        $className = $classNameNodeVisitor->getClassName();
        if (! is_string($className)) {
            return null;
        }

        return new ClassNames(
            $className,
            $classNameNodeVisitor->hasParentClassOrInterface(),
            $classNameNodeVisitor->getAttributes(),
        );
    }
}
