<?php

namespace App\Model\MockupProvider;

use ReflectionProperty;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractMockups implements MockupProviderInterface
{
    protected const string FEATURE = '';

    protected const array MOCKUPS = [];

    public function getFeature(): string
    {
        return static::FEATURE;
    }

    public function getMockups(): array
    {
        return static::MOCKUPS;
    }

    abstract public function getMockupParameter(?string $locale = null, ?string $feature = null): array;

    abstract public function getMockupVariables(array $parameters): array;

    protected function setId(object &$entity, int $id): void
    {
        $objectClass = get_class($entity);
        $reflectionProperty = new ReflectionProperty($objectClass, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($entity, $id);
    }
}
