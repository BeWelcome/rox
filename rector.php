<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
//        __DIR__ . '/build/admin',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        naming: true,
        privatization: true,
        typeDeclarations: true,
        rectorPreset: true,
    )
    ->withPhpSets()
;
/*
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
//        __DIR__ . '/build',
    ]);

    // Define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84, // Upgrade to PHP 8.4 features
        SetList::DEAD_CODE, // Remove dead code
        SetList::CODE_QUALITY, // General code quality improvements
        SetList::CODING_STYLE, // PSR-12 / PER coding style adjustments
        SetList::TYPE_DECLARATION, // Add types where possible
        SymfonySetList::SYMFONY_71, // Upgrade to Symfony 7.1 features (closest to 8.x available in standard sets, covers most attributes)
        SymfonySetList::SYMFONY_CODE_QUALITY, // Symfony specific quality rules
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION, // Use constructor property promotion
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES, // Migrate Doctrine Annotations to Attributes
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::DOCTRINE_ORM_300,

    ]);

    // Skip specific rules if needed during transition
    $rectorConfig->skip([
        // Example: If you want to delay adding void return types everywhere
        // AddVoidReturnTypeWhereNoReturnRector::class,
    ]);
};
*/
