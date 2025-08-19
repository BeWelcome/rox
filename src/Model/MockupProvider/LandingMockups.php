<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Member;
use App\Entity\NewLocation;
use App\Entity\Subtrip;
use App\Entity\Trip;
use Carbon\Carbon;

class LandingMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
        'be visited|none' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
        'be visited|two legs' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
    ];

    public function getFeature(): string
    {
        return 'landing';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        return match ($parameters['name']) {
            'be visited|none' => $this->getTripsWidgetEmpty(),
            'be visited|two legs' => $this->getTripsWidgetTwoLegs($parameters['user']),
            default => [],
        };
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }

    private function getTripsWidgetEmpty(): array
    {
        return [
            'legs' => null,
            'radius' => 10,
        ];
    }

    private function getTripsWidgetTwoLegs(Member $user): array
    {
        $trip = \Mockery::mock(Trip::class, [
            'getId' => 1,
            'getCreator' => $user,
            'getSummary' => 'Mocking Bird',
            'getDescription' => 'Mocking description',
            'getCountOfTravellers' => 2,
            'getAdditionalInfo' => TripAdditionalInfoType::NONE,
            'getCreated' => new \DateTime(),
        ]);
        $location = new NewLocation();
        $location->setName('Mock');
        $leg1 = \Mockery::mock(Subtrip::class, [
            'getId' => 1,
            'getArrival' => new Carbon(),
            'getDeparture' => new Carbon(),
            'getOptions' => [SubtripOptionsType::MEET_LOCALS],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $user,
            'getInvitationBy' => null,
        ]);
        $leg2 = \Mockery::mock(Subtrip::class, [
            'getId' => 2,
            'getArrival' => new Carbon(),
            'getDeparture' => new Carbon(),
            'getOptions' => [SubtripOptionsType::LOOKING_FOR_HOST],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $user,
            'getInvitationBy' => null,
        ]);

        return [
            'legs' => [
                $leg1,
                $leg2,
            ],
            'radius' => 10,
        ];
    }
}
