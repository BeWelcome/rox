<?php

namespace App\Command;

use App\Entity\BroadcastMessage;
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
    /**
     * @var string
     */
    protected static $defaultName = 'send:massmail';

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        Mailer $mailer
    ) {
        parent::__construct();
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
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
            $batchSize = $this->params->get('massmail_batch_size');
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
        if (!empty($scheduledBroadcastMessages)) {
            /** @var BroadcastMessage $scheduled */
            foreach ($scheduledBroadcastMessages as $scheduled) {
                $sender = $this->determineSender($scheduled->getNewsletter()->getType());
                $receiver = $scheduled->getReceiver();
                try {
                    $unsubscribeKey = '';
                    $newsletterType = $scheduled->getNewsletter()->getType();
                    $newsletterName = $scheduled->getNewsletter()->getName();
                    $parameters = [
                        'receiver' => $receiver,
                        'newsletter_type' => $newsletterType,
                        'subject' => strtolower('Broadcast_Title_' . $newsletterName),
                        'wordcode' => strtolower('Broadcast_Body_' . $newsletterName),
                    ];
                    if (
                        Newsletter::SPECIFIC_NEWSLETTER === $newsletterType
                        || Newsletter::REGULAR_NEWSLETTER === $newsletterType
                    ) {
                        try {
                            $unsubscribeKey = random_bytes(32);
                        } catch (Exception $e) {
                            $unsubscribeKey = openssl_random_pseudo_bytes(32);
                        }
                    }
                    $parameters['unsubscribe_key'] = bin2hex($unsubscribeKey);
                    $this->mailer->sendNewsletterEmail(
                        $sender,
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
//                    $scheduled->setStatus('Freeze');
                }
                $this->entityManager->persist($scheduled);
            }
            $this->entityManager->flush();
        }

        $io->success(sprintf('Sent %d messages', $sent));

        return 0;
    }

    private function determineSender($type): Address
    {
        switch ($type) {
            case 'RemindToLog':
            case 'MailToConfirmReminder':
                $sender = new Address('reminder@bewelcome.org', 'BeWelcome');
                break;
            case 'TermsOfUse':
                $sender = new Address('tou@bewelcome.org', 'BeWelcome');
                break;
            default:
                $sender = new Address('newsletter@bewelcome.org', 'BeWelcome');
        }

        return $sender;
    }
}
