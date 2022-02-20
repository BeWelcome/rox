<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use Carbon\Carbon;
use DateTime;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

class ErrorMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
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

    public function getFeature(): string
    {
        return 'errors';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        /** @var Member $user */
        $user = $parameters['user'];

        return [
            'username' => $user->getUsername(),
        ];
    }

    public function getMockupParameter(): array
    {
        return [];
    }
}
