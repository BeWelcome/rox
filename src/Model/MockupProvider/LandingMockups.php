<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Member;
use App\Entity\NewLocation;
use App\Entity\Subtrip;
use App\Entity\Trip;
use Carbon\Carbon;
use DateTime;
use ReflectionProperty;

class LandingMockups extends AbstractMockups
{
    protected const string FEATURE = 'landing';

    protected const array MOCKUPS = [
        'be visited|none' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
        'be visited|two legs' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
    ];

    public function getMockupVariables(array $parameters): array
    {
        switch ($parameters['name']) {
            case 'be visited|none':
                return $this->getTripsWidgetEmpty();
            case 'be visited|two legs':
                return $this->getTripsWidgetTwoLegs($parameters['user']);
            default:
                return [];
        }
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
        $trip = (new Trip())
            ->setCreator($user)
            ->setSummary('Mocking Bird')
            ->setDescription('Mocking description')
            ->setCountOfTravellers(2)
            ->setAdditionalInfo(TripAdditionalInfoType::NONE)
            ->setCreated(new DateTime())
        ;

        $reflectionProperty = new ReflectionProperty(Trip::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($trip, 1);

        $location = new NewLocation();
        $location->setName('Mock');

        $leg1 = (new SubTrip())
            ->setArrival(Carbon::instance(new DateTime('2021-02-22')))
            ->setDeparture(Carbon::instance(new DateTime('2021-02-24')))
            ->setLocation($location)
            ->setOptions([SubtripOptionsType::MEET_LOCALS])
            ->setTrip($trip)
            ->setInvitedBy($user)
        ;

        $leg2 = (new SubTrip())
            ->setArrival(Carbon::instance(new DateTime('2021-02-22')))
            ->setDeparture(Carbon::instance(new DateTime('2021-02-24')))
            ->setLocation($location)
            ->setOptions([SubtripOptionsType::MEET_LOCALS])
            ->setTrip($trip)
            ->setInvitedBy($user)
        ;

        $reflectionProperty = new ReflectionProperty(SubTrip::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($leg1, 1);
        $reflectionProperty->setValue($leg2, 2);

        return [
            'legs' => [
                $leg1,
                $leg2,
            ],
            'radius' => 10,
        ];
    }
}
