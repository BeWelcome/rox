<?php

namespace App\Model\MockupProvider;

class StatisticsMockups extends AbstractMockups
{
    protected const string FEATURE = 'statistics';

    protected const array MOCKUPS = [
        'statistics' => [
            'type' => 'page',
            'template' => 'about/statistics.html.twig',
            'description' => 'Statistics pages',
        ],
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMockupVariables(array $parameters): array
    {
        return [];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}
