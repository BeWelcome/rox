<?php

namespace App\Command;

use App\Entity\Preference;
use App\Model\ActivityModel;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * The daily, weekly or monthly mails about the activities created near the members who chose that in their
 * preferences (ActivitiesNotifications), like trips:notifications:send. Started by the servers' scheduler.
 */
#[AsCommand(
    name: 'activities:notifications:send',
    description: 'Send scheduled activity notification emails',
)]
class SendActivityNotificationsCommand extends SendScheduledNotificationsCommand
{
    public function __construct(
        private readonly ActivityModel $activityModel,
    ) {
        parent::__construct();
    }

    protected function preferenceValues(): array
    {
        return [
            'daily' => Preference::ACTIVITY_NOTIFICATIONS_DAILY,
            'weekly' => Preference::ACTIVITY_NOTIFICATIONS_WEEKLY,
            'monthly' => Preference::ACTIVITY_NOTIFICATIONS_MONTHLY,
        ];
    }

    protected function send(string $preferenceValue, DateTimeImmutable $createdSince, DateTimeImmutable $createdUntil): int
    {
        return $this->activityModel->sendScheduledActivityNotifications($preferenceValue, $createdSince, $createdUntil);
    }

    protected function kind(): string
    {
        return 'activity';
    }
}
