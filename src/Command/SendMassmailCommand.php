<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\BroadcastMessage;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\NewsletterStatistics;
use App\Repository\NewsletterRepository;
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
use Throwable;

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

    /**
     * Each message is sent in its own transaction, which locks its row while it is still ToSend: a volunteer who
     * stops the sending moves the waiting messages back to the queue meanwhile (MassmailModel::untrigger), and two
     * runs at the same time don't send a message twice. The counts change with the message.
     *
     * @param BroadcastMessage[] $scheduledBroadcastMessages
     */
    private function sendMassmail(array $scheduledBroadcastMessages, SymfonyStyle $io): int
    {
        $connection = $this->entityManager->getConnection();
        /** @var NewsletterStatisticsRepository $statistics */
        $statistics = $this->entityManager->getRepository(NewsletterStatistics::class);
        $sent = 0;
        // per mass mail: whether its text has images
        $hasImages = [];

        foreach ($scheduledBroadcastMessages as $scheduled) {
            $newsletter = $scheduled->getNewsletter();
            $receiver = $scheduled->getReceiver();
            $key = ['newsletter' => $newsletter->getId(), 'receiver' => $receiver->getId()];

            $connection->beginTransaction();
            try {
                $waiting = $connection->fetchOne(
                    "SELECT 1 FROM broadcastmessages WHERE IdBroadcast = :newsletter AND IdReceiver = :receiver AND Status = 'ToSend' FOR UPDATE",
                    $key,
                );
                if (false === $waiting) {
                    // stopped or sent by another run since the batch was read
                    $connection->rollBack();

                    continue;
                }

                // Skipped messages leave the queue, so that they don't take the place of others in the next runs
                if (!$this->sentToThisMember($receiver, $newsletter)) {
                    $this->setStatus($key, self::SKIPPED);
                    $statistics->add($newsletter, triggered: -1);
                    $connection->commit();

                    continue;
                }

                $parameters = ['unsubscribe_key' => bin2hex(random_bytes(32))];
                $hasImages[$newsletter->getId()] ??= $this->hasImages($newsletter);
                if ($hasImages[$newsletter->getId()]) {
                    $parameters['has_images'] = true;
                }
                try {
                    $this->mailer->sendNewsletterEmail($newsletter, $receiver, $parameters);
                    $this->setStatus($key, 'Sent', $parameters['unsubscribe_key']);
                    $statistics->add($newsletter, triggered: -1, sent: 1);
                    ++$sent;
                } catch (Exception $e) {
                    $io->error('Message Frozen: ' . $e->getMessage());
                    $this->setStatus($key, 'Freeze');
                    $statistics->add($newsletter, triggered: -1, failed: 1);
                }
                $connection->commit();
            } catch (Throwable $e) {
                $connection->rollBack();

                throw $e;
            }
        }

        return $sent;
    }

    /**
     * @param array{newsletter: int, receiver: int} $key
     */
    private function setStatus(array $key, string $status, ?string $unsubscribeKey = null): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'UPDATE broadcastmessages SET Status = :status, unsubscribe_key = COALESCE(:unsubscribeKey, unsubscribe_key), updated = NOW()
            WHERE IdBroadcast = :newsletter AND IdReceiver = :receiver',
            $key + ['status' => $status, 'unsubscribeKey' => $unsubscribeKey],
        );
    }

    private function hasImages(Newsletter $newsletter): bool
    {
        /** @var NewsletterRepository $newsletters */
        $newsletters = $this->entityManager->getRepository(Newsletter::class);
        $translations = $newsletters->getTranslations($newsletter);
        $any = reset($translations);

        return false !== $any && str_contains((string) $any['body'], '<figure');
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
