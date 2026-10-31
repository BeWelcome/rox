<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\BroadcastMessage;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\NewsletterStatistics;
use App\Repository\NewsletterStatisticsRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'send:massmail',
    description: 'Send a batch of massmail email everytime the command is called',
    aliases: [],
    hidden: false,
)]
class SendMassmailCommand extends Command
{
    /** A message to a member who can't receive the mass mail any longer. */
    public const string SKIPPED = 'Skipped';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Mailer $mailer,
        private readonly int $batchSize,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // The crontabs call "send:massmail 100", as main's command takes the batch size as argument
        $this->addArgument('batchSize', InputArgument::OPTIONAL, 'Count of mails sent while the command is running (default: massmail_batch_size)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = (int) $input->getArgument('batchSize') ?: $this->batchSize;

        $massmailRepository = $this->entityManager->getRepository(BroadcastMessage::class);

        /** @var BroadcastMessage[] $scheduledBroadcastMessages */
        $scheduledBroadcastMessages = $massmailRepository->findBy(
            ['status' => 'ToSend'],
            ['updated' => 'ASC'],
            $batchSize,
            0
        );

        $sent = 0;
        if (!empty($scheduledBroadcastMessages)) {
            $sent = $this->sendMassmail($scheduledBroadcastMessages, $io);
        }

        $io->success(\sprintf('Sent %d messages', $sent));

        return 0;
    }

    private function sendMassmail(array $scheduledBroadcastMessages, SymfonyStyle $io): int
    {
        $sent = 0;
        $lastBroadcastId = 0;
        // per mass mail: [sent, failed, skipped]
        $statistics = [];

        /** @var BroadcastMessage $scheduled */
        foreach ($scheduledBroadcastMessages as $scheduled) {
            $parameters = [];
            if ($lastBroadcastId !== $scheduled->getNewsletter()->getId()) {
                // Check if the current newsletter contains images and set the parameter
                $newsletterRepository = $this->entityManager->getRepository(Newsletter::class);
                $newsletterTranslations = $newsletterRepository->getTranslations($scheduled->getNewsletter());
                $anyNewsletter = reset($newsletterTranslations);

                $hasImages = str_contains((string) $anyNewsletter['body'], '<figure');
                if ($hasImages) {
                    $parameters['has_images'] = true;
                }

                $lastBroadcastId = $scheduled->getNewsletter()->getId();
            }
            $newsletterId = $scheduled->getNewsletter()->getId();
            $receiver = $scheduled->getReceiver();

            // Skipped messages leave the queue, so that they don't take the place of others in the next runs
            if (!$this->sentToThisMember($receiver, $scheduled->getNewsletter())) {
                $scheduled->setStatus(self::SKIPPED);
                $statistics[$newsletterId][2] = ($statistics[$newsletterId][2] ?? 0) + 1;
                $this->entityManager->persist($scheduled);

                continue;
            }

            try {
                $unsubscribeKey = bin2hex(random_bytes(32));
                $parameters['unsubscribe_key'] = $unsubscribeKey;
                $this->mailer->sendNewsletterEmail(
                    $scheduled->getNewsletter(),
                    $receiver,
                    $parameters
                );

                $scheduled
                    ->setStatus('Sent')
                    ->setUnsubscribeKey($unsubscribeKey);
                ++$sent;
                $statistics[$newsletterId][0] = ($statistics[$newsletterId][0] ?? 0) + 1;
            } catch (Exception $e) {
                $io->error('Message Frozen: ' . $e->getMessage());
                $scheduled->setStatus('Freeze');
                $statistics[$newsletterId][1] = ($statistics[$newsletterId][1] ?? 0) + 1;
            }
            $this->entityManager->persist($scheduled);
        }
        $this->entityManager->flush();

        /** @var NewsletterStatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(NewsletterStatistics::class);
        foreach ($statistics as $newsletterId => $counts) {
            $done = ($counts[0] ?? 0) + ($counts[1] ?? 0) + ($counts[2] ?? 0);
            $statisticsRepository->add($newsletterId, triggered: -$done, sent: $counts[0] ?? 0, failed: $counts[1] ?? 0);
        }

        return $sent;
    }

    /**
     * The member can still receive the mass mail: enqueued members may have left or been suspended since. A change
     * of the terms of use goes to all active members, the others to those who get messages.
     */
    private function sentToThisMember(Member $receiver, Newsletter $newsletter): bool
    {
        $statuses = Newsletter::TERMS_OF_USE === $newsletter->getType()
            ? MemberStatusType::ACTIVE_ALL_ARRAY
            : MemberStatusType::ACTIVE_WITH_MESSAGES_ARRAY;

        return \in_array($receiver->getStatus(), $statuses, true);
    }
}
