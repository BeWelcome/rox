<?php

namespace App\Model\MockupProvider;

use App\Doctrine\LegOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Leg;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\Trip;
use Carbon\Carbon;
use DateTime;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * The mails about trips near a member: the one sent right away for a leg, and the daily, weekly or monthly digest.
 */
#[AutoconfigureTag('app.mockup_provider')]
class TripMockups implements MockupProviderInterface
{
    use MockupEntities;

    private const string DIGEST = 'trips near you, digest (mail)';

    private const array MOCKUPS = [
        'trip near you (mail)' => [
            'type' => 'email',
            'template' => 'emails/trip.notification.new.html.twig',
            'description' => 'Mail to members near a leg of a new trip (who want to hear right away).',
        ],
        self::DIGEST => [
            'type' => 'email',
            'template' => 'emails/trip.notification.digest.html.twig',
            'description' => 'Daily, weekly or monthly mail with the new trips near a member. The legs have 1, 0, 2, 5 and 3 nights.',
            'with_parameters' => true,
        ],
    ];

    public function getFeature(): string
    {
        return 'trips';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        // the count picks the plural form of the subject; five legs show every plural form of the nights
        return self::DIGEST === $feature ? [
            'frequency' => [
                'daily' => Preference::TRIP_NOTIFICATIONS_DAILY,
                'weekly' => Preference::TRIP_NOTIFICATIONS_WEEKLY,
                'monthly' => Preference::TRIP_NOTIFICATIONS_MONTHLY,
            ],
            'count' => self::pluralCounts($locale),
        ] : [];
    }

    public function getMockupVariables(array $parameters): array
    {
        $legs = $this->legs($parameters['admin']);

        return match ($parameters['name']) {
            'trip near you (mail)' => [
                'sender' => $parameters['admin'],
                'receiver' => $parameters['user'],
                'trip' => $legs[0]->getTrip(),
                'leg' => $legs[0],
                'subject' => 'Subject',
            ],
            self::DIGEST => [
                'receiver' => $parameters['user'],
                'legs' => \array_slice($legs, 0, max(1, (int) ($parameters['count'] ?? 5))),
                'frequency' => strtolower((string) ($parameters['frequency'] ?? Preference::TRIP_NOTIFICATIONS_DAILY)),
                'subject' => 'Subject',
            ],
            default => [],
        };
    }

    /**
     * Five trips of the traveller with a leg near the receiver, some looking for a host, some to meet locals. Their
     * nights (1, 0, 2, 5, 3) give every plural form of "nights" an example.
     *
     * @return list<Leg>
     */
    private function legs(Member $traveller): array
    {
        $location = new Location();
        $location->setName('Mock');
        $legs = [];
        $trips = [
            1 => ['Mocking Bird', LegOptionsType::LOOKING_FOR_HOST, 'next friday', 1, 2],
            2 => ['Passing through', LegOptionsType::MEET_LOCALS, 'next saturday', 0, 1],
            3 => ['Cycling along the river', LegOptionsType::LOOKING_FOR_HOST, 'next friday +1 week', 2, 1],
            4 => ['A week in town', LegOptionsType::LOOKING_FOR_HOST, 'next monday +2 weeks', 5, 3],
            5 => ['Long weekend', LegOptionsType::MEET_LOCALS, 'next friday +3 weeks', 3, 2],
        ];
        foreach ($trips as $id => [$summary, $option, $arrival, $nights, $travellers]) {
            $trip = self::withId(new Trip(), $id)
                ->setCreator($traveller)
                ->setSummary($summary)
                ->setDescription('Mocking description')
                ->setCountOfTravellers($travellers)
                ->setAdditionalInfo(TripAdditionalInfoType::NONE)
                ->setCreated(new DateTime());
            $leg = self::withId(new Leg(), $id)
                ->setArrival(new Carbon($arrival))
                ->setDeparture(new Carbon($arrival)->addDays($nights))
                ->setOptions([$option])
                ->setLocation($location);
            $trip->addLeg($leg);
            $legs[] = $leg;
        }

        return $legs;
    }
}
