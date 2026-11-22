<?php

namespace App\Model\MockupProvider;

use App\Entity\Activity;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use Carbon\Carbon;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * The mails about activities near a member: the one sent right away for an activity, and the daily, weekly or
 * monthly digest.
 */
#[AutoconfigureTag('app.mockup_provider')]
class ActivityMockups implements MockupProviderInterface
{
    use MockupEntities;

    private const string DIGEST = 'activities near you, digest (mail)';

    private const array MOCKUPS = [
        'activity near you (mail)' => [
            'type' => 'email',
            'template' => 'emails/activity.notification.new.html.twig',
            'description' => 'Mail to members near a new activity (who want to hear right away).',
        ],
        self::DIGEST => [
            'type' => 'email',
            'template' => 'emails/activity.notification.digest.html.twig',
            'description' => 'Daily, weekly or monthly mail with the new activities near a member.',
            'with_parameters' => true,
        ],
    ];

    public function getFeature(): string
    {
        return 'activities';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        // the count picks the plural form of the subject
        return self::DIGEST === $feature ? [
            'frequency' => [
                'daily' => Preference::ACTIVITY_NOTIFICATIONS_DAILY,
                'weekly' => Preference::ACTIVITY_NOTIFICATIONS_WEEKLY,
                'monthly' => Preference::ACTIVITY_NOTIFICATIONS_MONTHLY,
            ],
            'count' => self::pluralCounts($locale),
        ] : [];
    }

    public function getMockupVariables(array $parameters): array
    {
        $activities = $this->activities($parameters['admin']);

        return match ($parameters['name']) {
            'activity near you (mail)' => [
                'sender' => $parameters['admin'],
                'receiver' => $parameters['user'],
                'activity' => $activities[0],
                'subject' => 'Subject',
            ],
            self::DIGEST => [
                'receiver' => $parameters['user'],
                'activities' => \array_slice($activities, 0, max(1, (int) ($parameters['count'] ?? 5))),
                'frequency' => strtolower((string) ($parameters['frequency'] ?? Preference::ACTIVITY_NOTIFICATIONS_DAILY)),
                'subject' => 'Subject',
            ],
            default => [],
        };
    }

    /**
     * Five activities the creator organizes near the receiver.
     *
     * @return list<Activity>
     */
    private function activities(Member $creator): array
    {
        $location = new Location();
        $location->setName('Mock');
        $activities = [];
        $planned = [
            1 => ['Picnic in the park', 'Main entrance', 'next saturday 14:00', 'next saturday 18:00'],
            2 => ['Board games evening', 'Community centre', 'next saturday +1 week 19:00', 'next saturday +1 week 22:30'],
            3 => ['Walk along the river', '', 'next sunday +1 week 10:00', 'next sunday +1 week 13:00'],
            4 => ['Language exchange', 'Library, first floor', 'next wednesday +2 weeks 18:00', 'next wednesday +2 weeks 20:00'],
            5 => ['Weekend hike', 'Train station', 'next saturday +3 weeks 08:00', 'next sunday +3 weeks 17:00'],
        ];
        foreach ($planned as $id => [$title, $address, $starts, $ends]) {
            $activities[] = self::withId(new Activity(), $id)
                ->setCreatedBy($creator)
                ->setTitle($title)
                ->setDescription('Bring something to share.')
                ->setLocation($location)
                ->setAddress($address)
                ->setStarts(new Carbon($starts))
                ->setEnds(new Carbon($ends))
                ->setStatus(Activity::STATUS_ACTIVE);
        }

        return $activities;
    }
}
