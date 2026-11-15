<?php

namespace App\Command;

use App\Entity\Preference;
use App\Model\ActivityModel;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The daily, weekly or monthly mails about the activities created near the members who chose that in their
 * preferences (ActivitiesNotifications), like trips:notifications:send. Started by the servers' scheduler.
 */
#[AsCommand(
    name: 'activities:notifications:send',
    description: 'Send scheduled activity notification emails',
)]
class SendActivityNotificationsCommand extends Command
{
    private const array FREQUENCIES = [
        'daily' => [Preference::ACTIVITY_NOTIFICATIONS_DAILY, 'P1D'],
        'weekly' => [Preference::ACTIVITY_NOTIFICATIONS_WEEKLY, 'P7D'],
        'monthly' => [Preference::ACTIVITY_NOTIFICATIONS_MONTHLY, 'P31D'],
    ];

    public function __construct(
        private readonly ActivityModel $activityModel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('frequency', InputArgument::REQUIRED, 'daily, weekly or monthly');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $frequency = strtolower((string) $input->getArgument('frequency'));

        if (!isset(self::FREQUENCIES[$frequency])) {
            $io->error('Frequency must be daily, weekly or monthly.');

            return Command::INVALID;
        }

        [$preferenceValue, $period] = self::FREQUENCIES[$frequency];
        $createdUntil = new DateTimeImmutable();
        $createdSince = $createdUntil->sub(new DateInterval($period));
        $sent = $this->activityModel->sendScheduledActivityNotifications($preferenceValue, $createdSince, $createdUntil);

        $io->success(\sprintf('Sent %d activity notification emails.', $sent));

        return Command::SUCCESS;
    }
}
