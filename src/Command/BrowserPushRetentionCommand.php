<?php

namespace App\Command;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Repository\BrowserPushNotificationRepository;
use App\Repository\BrowserPushSubscriptionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'browser-push:retention',
    description: 'Remove expired browser push notifications and inactive subscriptions',
)]
final class BrowserPushRetentionCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var BrowserPushNotificationRepository $notificationRepository */
        $notificationRepository = $this->entityManager->getRepository(BrowserPushNotification::class);
        /** @var BrowserPushSubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = $this->entityManager->getRepository(BrowserPushSubscription::class);

        $notifications = $notificationRepository->deleteNotificationsOlderThan(new DateTimeImmutable('-7 days'));
        $subscriptions = $subscriptionRepository->deleteInactiveSubscriptionsOlderThan(new DateTimeImmutable('-1 year'));

        new SymfonyStyle($input, $output)->success(\sprintf(
            'Removed %d browser push notifications and %d inactive subscriptions.',
            $notifications,
            $subscriptions
        ));

        return Command::SUCCESS;
    }
}
