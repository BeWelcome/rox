<?php

namespace App\Command;

use App\Entity\BroadcastMessage;
use App\Utilities\MailerTrait;
use App\Utilities\MessageTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;

class SendMassmailCommand extends Command
{
    use TranslatorTrait;
    use MailerTrait;

    private $params;

    protected static $defaultName = 'send:massmail';

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->params = $params;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a batch of massmail email everytime the command is called')
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'Count of mails send while the command is running')
        ;
    }

    private function determineSender($type): Address
    {
        switch($type) {
            case "RemindToLog":
            case "MailToConfirmReminder":
                $sender = new Address("reminder@bewelcome.org", "BeWelcome");
                break;
            case "TermsOfUse":
                $sender = new Address("tou@bewelcome.org", "BeWelcome");
                break;
            default:
                $sender = new Address("newsletter@bewelcome.org", "BeWelcome");
        }
        return $sender;
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
        $scheduledBroadcastMessages = $massmailRepository->findBy(['status' => 'ToSend'], ['updated' => 'ASC'], $batchSize, 0);

        $sent = 0;
        if (!empty($scheduledBroadcastMessages))
        {
            /** @var BroadcastMessage $scheduled */
            foreach($scheduledBroadcastMessages as $scheduled)
            {
                $sender = $this->determineSender($scheduled->getNewsletter()->getType());
                $receiver = $scheduled->getReceiver();
                try {
                    $this->sendTemplateEmail($sender, $receiver, 'newsletter', [
                        'receiver' => $receiver,
                        'subject' => strtolower('Broadcast_Title_' . $scheduled->getNewsletter()->getName()),
                        'wordcode' => strtolower('Broadcast_Body_' . $scheduled->getNewsletter()->getName()),
                    ]);
                    $scheduled->setStatus('Sent');
                    $sent++;
                } catch(Exception $e) {
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
