<?php

namespace App\Model\MockupProvider;

interface MockupProviderInterface
{
    public function getFeature(): string;

    public function getMockups(): array;

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array;

    public function getMockupVariables(array $parameters): array;
}
