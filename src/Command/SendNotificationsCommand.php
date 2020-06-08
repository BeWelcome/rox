<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Doctrine\NotificationStatusType;
use App\Entity\ForumPost;
use App\Entity\PostNotification;
use App\Repository\PostNotificationRepository;
use App\Utilities\MailerTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;

class SendNotificationsCommand extends Command
{
    use TranslatorTrait;
    use MailerTrait;

    /**
     * @var string
     */
    protected static $defaultName = 'send:notifications';

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params, LoggerInterface $logger)
    {
        parent::__construct();
        $this->params = $params;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    protected function configure()
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
            $batchSize = $this->params->get('forum_notification_batch_size');
        }

        /** @var PostNotificationRepository $notificationQueue */
        $notificationQueue = $this->entityManager->getRepository(PostNotification::class);
        /** @var PostNotification[] $scheduled */
        $scheduledNotifications = $notificationQueue->getScheduledNotifications($batchSize);

        $sent = 0;
        if (!empty($scheduledNotifications)) {
            /** @var PostNotification $scheduled */
            foreach ($scheduledNotifications as $scheduled) {
                $receiver = $scheduled->getReceiver();
                $status = $receiver->getStatus();
                if (!in_array($status, MemberStatusType::ACTIVE_ALL_ARRAY, true )) {
                    continue;
                }

                try {
                    // Force locale for all methods
                    $this->setTranslatorLocale($receiver);
                    $sender = $this->determineSender($scheduled->getPost());
                    $subject = $this->getSubject($scheduled);
                    $this->sendTemplateEmail($sender, $receiver, 'notifications', [
                        'subject' => $subject,
                        'notification' => $scheduled,
                    ]);
                    $scheduled->setStatus(NotificationStatusType::SENT);
                    ++$sent;
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                    $scheduled->setStatus(NotificationStatusType::FROZEN);
                }
                $this->entityManager->persist($scheduled);
            }
            $this->entityManager->flush();
        }

        $io->success(sprintf('Sent %d messages', $sent));

        return 0;
    }

    private function determineSender(ForumPost $post)
    {
        $thread = $post->getThread();
        if ($thread->getGroup()) {
            $from = new Address('group@bewelcome.org', 'BeWelcome - ' . $post->getAuthor()->getUsername());
        } else {
            $from = new Address('forum@bewelcome.org', 'BeWelcome - ' . $post->getAuthor()->getUsername());
        }

        return $from;
    }

    private function getSubject(PostNotification $notification)
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
