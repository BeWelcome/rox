<?php

namespace App\Model\MockupProvider;

interface MockupProviderInterface
{
    public function getFeature(): string;

    public function getMockups(): array;

    public function getMockupParameter(): array;

    public function getMockupVariables(array $parameters): array;
}
