<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\BroadcastMessage;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Service\Mailer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;

class SendMassmailCommand extends Command
{
    protected static $defaultName = 'send:massmail';

    private EntityManagerInterface $entityManager;
    private Mailer $mailer;
    private int $batchSize;

    public function __construct(
        EntityManagerInterface $entityManager,
        Mailer $mailer,
        int $batchSize
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->batchSize = $batchSize;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a batch of massmail email everytime the command is called')
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'Count of mails send while the command is running')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = $input->getArgument('batchSize');

        if (!$batchSize) {
            // use default from service configuration if not given as option on command call
            $batchSize = $this->batchSize;
        }

        $massmailRepository = $this->entityManager->getRepository(BroadcastMessage::class);
        /** @var BroadcastMessage[] $scheduled */
        $scheduledBroadcastMessages = $massmailRepository->findBy(
            ['status' => 'ToSend'],
            ['updated' => 'ASC'],
            $batchSize,
            0
        );

        $sent = 0;
        $lastBroadcastId = 0;
        if (!empty($scheduledBroadcastMessages)) {
            /** @var BroadcastMessage $scheduled */
            foreach ($scheduledBroadcastMessages as $scheduled) {
                $parameters = [];
                if ($lastBroadcastId != $scheduled->getNewsletter()->getId()) {
                    // Check if the current newsletter contains images and set the parameter
                    $newsletterTranslations = $scheduled->getNewsletter()->getTranslations();
                    $anyNewsletter = reset($newsletterTranslations);

                    $hasImages = false !== strpos($anyNewsletter['body'], "<figure");
                    if ($hasImages) {
                        $parameters['has_images'] = true;
                    }

                    $lastBroadcastId = $scheduled->getNewsletter()->getId();
                }
                $receiver = $scheduled->getReceiver();
                $status = $receiver->getStatus();
                if (
                    (MemberStatusType::SUSPENDED === $status && $receiver->getRemindersWithOutLogin() !== 100)
                    && MemberStatusType::ACTIVE !== $status
                    && MemberStatusType::OUT_OF_REMIND !== $status
                    && MemberStatusType::CHOICE_INACTIVE !== $status
                ) {
                    // Only send messages to members that are active or have just been suspended RemindersWithoutLogin
                    // is set to 100 on suspension
                    continue;
                }
                try {
                    try {
                        $unsubscribeKey = random_bytes(32);
                    } catch (Exception $e) {
                        $unsubscribeKey = openssl_random_pseudo_bytes(32);
                    }

                    $parameters['unsubscribe_key'] = $unsubscribeKey;
                    $this->mailer->sendNewsletterEmail(
                        $scheduled->getNewsletter(),
                        $receiver,
                        $parameters
                    );

                    $scheduled
                        ->setStatus('Sent')
                        ->setUnsubscribeKey(bin2hex($unsubscribeKey))
                    ;
                    ++$sent;
                } catch (Exception $e) {
                    $io->error('Message Frozen: ' . $e->getMessage());
                    $scheduled->setStatus('Freeze');
                }
                $this->entityManager->persist($scheduled);
            }
            $this->entityManager->flush();
        }

        $io->success(sprintf('Sent %d messages', $sent));

        return 0;
    }
}
