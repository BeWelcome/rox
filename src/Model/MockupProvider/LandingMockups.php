<?php

namespace App\Model\MockupProvider;

use App\Doctrine\LegOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Leg;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Trip;
use Carbon\Carbon;
use DateTime;

class LandingMockups implements MockupProviderInterface
{
    use MockupEntities;

    private const array MOCKUPS = [
        'be visited|none' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
        'be visited|two legs' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
        'trip near you (mail)' => [
            'type' => 'email',
            'template' => 'emails/trip.notification.new.html.twig',
            'description' => 'Mail to members near a leg of a new trip.',
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
            'trip near you (mail)' => $this->getTripMail($parameters['admin'], $parameters['user']),
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

    /**
     * The traveller (bwadmin) passes near the receiver (the translator).
     */
    private function getTripMail(Member $traveller, Member $receiver): array
    {
        $legs = $this->getTripsWidgetTwoLegs($traveller)['legs'];

        return [
            'sender' => $traveller,
            'receiver' => $receiver,
            'trip' => $legs[0]->getTrip(),
            'leg' => $legs[0],
            'subject' => 'Subject',
        ];
    }

    private function getTripsWidgetTwoLegs(Member $user): array
    {
        $trip = self::withId(new Trip(), 1)
            ->setCreator($user)
            ->setSummary('Mocking Bird')
            ->setDescription('Mocking description')
            ->setCountOfTravellers(2)
            ->setAdditionalInfo(TripAdditionalInfoType::NONE)
            ->setCreated(new DateTime());
        $location = new Location();
        $location->setName('Mock');
        $legs = [];
        foreach ([1 => LegOptionsType::MEET_LOCALS, 2 => LegOptionsType::LOOKING_FOR_HOST] as $id => $option) {
            $leg = self::withId(new Leg(), $id)
                ->setArrival(new Carbon())
                ->setDeparture(new Carbon())
                ->setOptions([$option])
                ->setLocation($location)
                ->setInvitedBy($user);
            $trip->addLeg($leg);
            $legs[] = $leg;
        }

        return [
            'legs' => $legs,
            'radius' => 10,
        ];
    }
}
