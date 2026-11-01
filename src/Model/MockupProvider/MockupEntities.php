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
}
