<?php

namespace App\Model\MockupProvider;

use ReflectionClass;

/**
 * The test data of the translators' previews are entities with test values, not mocks: the mocking library is a
 * development package, which the servers don't install. Entities that aren't stored have no id; the templates link
 * to them by id.
 */
trait MockupEntities
{
    /**
     * @template T of object
     *
     * @param T $entity
     *
     * @return T
     */
    private static function withId(object $entity, int $id): object
    {
        $class = new ReflectionClass($entity);
        while (!$class->hasProperty('id') && false !== $class->getParentClass()) {
            $class = $class->getParentClass();
        }
        $class->getProperty('id')->setValue($entity, $id);

        return $entity;
    }

    /**
     * How many of something a mockup should show so that a language's plural forms all get an example (never none):
     * one and some for the languages with two forms, one, two and five for the others.
     *
     * @return array<string, int> a label for the variant => the count
     */
    private static function pluralCounts(?string $locale): array
    {
        return match ($locale) {
            'en', 'de', 'fr', 'es', 'pt', 'pt-br', 'gl' => ['one' => 1, 'some' => 3],
            default => ['one' => 1, 'few' => 2, 'other' => 5],
        };
    }
}
