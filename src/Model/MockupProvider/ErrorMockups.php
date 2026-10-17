<?php

namespace App\Model\MockupProvider;

use App\Entity\Member;

class ErrorMockups extends AbstractMockups
{
    protected const string FEATURE = 'errors';

    protected const array MOCKUPS = [
        'error 403' => [
            'type' => 'page',
            'template' => 'bundles/TwigBundle/Exception/error403.html.twig',
            'description' => 'Access to a resource was denied.',
        ],
        'error 404' => [
            'type' => 'page',
            'template' => 'bundles/TwigBundle/Exception/error404.html.twig',
            'description' => 'The page doesn\'t exists.',
        ],
        'error 500' => [
            'type' => 'page',
            'template' => 'bundles/TwigBundle/Exception/error500.html.twig',
            'description' => 'A server problem (something bad happened).',
        ],
    ];

    public function getMockupVariables(array $parameters): array
    {
        /** @var Member $user */
        $user = $parameters['user'];

        return [
            'username' => $user->getUsername(),
        ];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}
