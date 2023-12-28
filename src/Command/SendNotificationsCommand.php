<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Doctrine\NotificationStatusType;
use App\Entity\ForumPost;
use App\Entity\PostNotification;
use App\Repository\PostNotificationRepository;
use App\Service\Mailer;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * The execute method which does all the work is understandable. The high coupling stems in the framework.
 */
class SendNotificationsCommand extends Command
{
    use TranslatorTrait;

    protected static $defaultName = 'send:notifications';

    private ParameterBagInterface $params;

    private EntityManagerInterface $entityManager;

    private LoggerInterface $logger;

    private Mailer $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        Mailer $mailer,
        int $batchSize
    ) {
        parent::__construct();
        $this->batchSize = $batchSize;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send a batch of notification email every time the command is called')
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'Count of mails send while the command is running')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->notice('Started the export');

        $io = new SymfonyStyle($input, $output);
        $batchSize = $input->getArgument('batchSize');

        if (!$batchSize) {
            $batchSize = $this->batchSize;
        }

        /** @var PostNotificationRepository $notificationQueue */
        $notificationQueue = $this->entityManager->getRepository(PostNotification::class);
        $scheduledNotifications = $notificationQueue->getScheduledNotifications($batchSize);

        $sent = 0;
        if (!empty($scheduledNotifications)) {
            /** @var PostNotification $scheduled */
            foreach ($scheduledNotifications as $scheduled) {
                $receiver = $scheduled->getReceiver();
                $status = $receiver->getStatus();
                $notificationStatus = NotificationStatusType::FROZEN;
                if (\in_array($status, MemberStatusType::ACTIVE_ALL_ARRAY, true)) {
                    try {
                        // Force locale for all methods
                        $this->setTranslatorLocale($receiver);
                        $sender = $this->determineSender($scheduled->getPost());
                        $subject = $this->getSubject($scheduled);
                        $this->mailer->sendNotificationEmail(
                            $sender,
                            $receiver,
                            [
                                'subject' => $subject,
                                'notification' => $scheduled,
                                'datesent' => $scheduled->getCreated(),
                            ]
                        );
                        $notificationStatus = NotificationStatusType::SENT;
                        ++$sent;
                    } catch (\Exception $e) {
                        $io->error($e->getMessage());
                    }
                }
                $scheduled->setStatus($notificationStatus);
                $this->entityManager->persist($scheduled);
            }
            $this->entityManager->flush();
            $io->success(
                sprintf(
                    'Sent %d messages, skipped %d messages',
                    $sent,
                    \count($scheduledNotifications) - $sent
                )
            );
        } else {
            $io->success('No messages to be sent');
        }

        return 0;
    }

    private function determineSender(ForumPost $post): Address
    {
        $thread = $post->getThread();
        if ($thread->getGroup()) {
            $from = new Address('group@bewelcome.org', 'BeWelcome - ' . $post->getAuthor()->getUsername());
        } else {
            $from = new Address('forum@bewelcome.org', 'BeWelcome - ' . $post->getAuthor()->getUsername());
        }

        return $from;
    }

    private function getSubject(PostNotification $notification): string
    {
        $prefix = '';
        switch ($notification->getType()) {
            case 'reply':
                $prefix = 'Re: ';
                break;
            case 'moderatoraction':
            case 'deletepost':
            case 'deletethread':
            case 'useredit':
                    $prefix = $this->getTranslator()->trans('forummailboteditedpost');
                break;
            case 'buggy':
            default:
                break;
        }
        $subject = $prefix . $notification->getPost()->getThread()->getTitle();
        if ($notification->getPost()->getThread()->getGroup()) {
            $subject .= ' [' . $notification->getPost()->getThread()->getGroup()->getName() . ']';
        }

        return strip_tags($subject);
    }
}
