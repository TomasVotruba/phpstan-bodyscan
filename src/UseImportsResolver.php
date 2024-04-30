<?php

declare(strict_types=1);

namespace TomasVotruba\PHPStanBodyscan;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use TomasVotruba\PHPStanBodyscan\NodeDecorator\FullyQualifiedNameNodeDecorator;
use TomasVotruba\PHPStanBodyscan\NodeVisitor\UsedClassNodeVisitor;

/**
 * @see \TomasVotruba\PHPStanBodyscan\Tests\UseImportsResolver\UseImportsResolverTest
 */
final readonly class UseImportsResolver
{
    public function __construct(
        private Parser $parser,
        private FullyQualifiedNameNodeDecorator $fullyQualifiedNameNodeDecorator,
    ) {
    }

    /**
     * @return string[]
     */
    public function resolve(string $filePath): array
    {
        /** @var string $fileContents */
        $fileContents = file_get_contents($filePath);

        $stmts = $this->parser->parse($fileContents);
        if ($stmts === null) {
            return [];
        }

        $this->fullyQualifiedNameNodeDecorator->decorate($stmts);

        $nodeTraverser = new NodeTraverser();
        $usedClassNodeVisitor = new UsedClassNodeVisitor();
        $nodeTraverser->addVisitor($usedClassNodeVisitor);
        $nodeTraverser->traverse($stmts);

        return $usedClassNodeVisitor->getUsedNames();
    }
}
