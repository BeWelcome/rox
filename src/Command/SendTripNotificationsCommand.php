<?php

namespace App\Command;

use App\Entity\Preference;
use App\Model\TripModel;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'trips:notifications:send',
    description: 'Send scheduled trip notification emails',
)]
class SendTripNotificationsCommand extends SendScheduledNotificationsCommand
{
    public function __construct(
        private readonly TripModel $tripModel,
    ) {
        parent::__construct();
    }

    protected function preferenceValues(): array
    {
        return [
            'daily' => Preference::TRIP_NOTIFICATIONS_DAILY,
            'weekly' => Preference::TRIP_NOTIFICATIONS_WEEKLY,
            'monthly' => Preference::TRIP_NOTIFICATIONS_MONTHLY,
        ];
    }

    protected function send(string $preferenceValue, DateTimeImmutable $createdSince, DateTimeImmutable $createdUntil): int
    {
        return $this->tripModel->sendScheduledTripNotifications($preferenceValue, $createdSince, $createdUntil);
    }

    protected function kind(): string
    {
        return 'trip';
    }
}
