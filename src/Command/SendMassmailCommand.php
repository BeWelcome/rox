<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\BroadcastMessage;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Mailer $mailer,
        private readonly int $batchSize,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $massmailRepository = $this->entityManager->getRepository(BroadcastMessage::class);
        /** @var BroadcastMessage[] $scheduled */
        $scheduledBroadcastMessages = $massmailRepository->findBy(
            ['status' => 'ToSend'],
            ['updated' => 'ASC'],
            $this->batchSize,
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
            $receiver = $scheduled->getReceiver();
            $status = $receiver->getStatus();

            // Only send messages to members that are active or have just been suspended RemindersWithoutLogin
            // is set to 100 on suspension
            if (!$this->sentToThisMember($receiver, $status)) {
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
            } catch (Exception $e) {
                $io->error('Message Frozen: ' . $e->getMessage());
                $scheduled->setStatus('Freeze');
            }
            $this->entityManager->persist($scheduled);
        }
        $this->entityManager->flush();

        return $sent;
    }

    private function sentToThisMember(Member $receiver, string $status): bool
    {
        return !(
            (MemberStatusType::SUSPENDED === $status && 100 !== $receiver->getRemindersWithOutLogin())
            && MemberStatusType::ACTIVE !== $status
            && MemberStatusType::OUT_OF_REMIND !== $status
            && MemberStatusType::CHOICE_INACTIVE !== $status
        );
    }
}
