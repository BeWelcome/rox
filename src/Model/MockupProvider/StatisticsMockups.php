<?php

namespace App\Model\MockupProvider;

class StatisticsMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
        'statistics' => [
            'type' => 'page',
            'template' => 'about/statistics.html.twig',
            'description' => 'Statistics pages',
        ],
    ];

    public function getFeature(): string
    {
        return 'statistics';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        return [];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}
