<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Doctrine\NotificationStatusType;
use App\Entity\ForumPost;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\PostNotification;
use App\Model\ForumModel;
use App\Repository\PostNotificationRepository;
use App\Service\BrowserNotificationPayload;
use App\Service\BrowserNotificationService;
use App\Service\BrowserPushNotificationProcessor;
use App\Service\Mailer;
use App\Utilities\TranslatorTrait;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * The execute method which does all the work is understandable. The high coupling stems in the framework.
 */
#[AsCommand(
    name: 'send:notifications',
    description: 'Send notification emails and browser push notifications',
    aliases: [],
    hidden: false,
)]
class SendAndPushNotificationsCommand extends Command
{
    use TranslatorTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private Mailer $mailer,
        private BrowserNotificationService $browserNotificationService,
        private UrlGeneratorInterface $urlGenerator,
        private BrowserPushNotificationProcessor $browserPushNotificationProcessor,
        private UriSigner $uriSigner,
        private int $batchSize,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'Number of notifications sent per run')
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
            $notificationReferences = [];
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
                        $thread = $scheduled->getPost()->getThread();
                        $referenceKey = $thread->getId() . '-' . $receiver->getId();
                        $previousMessageIds = $notificationReferences[$referenceKey]
                            ??= $notificationQueue->getSentMessageIds($receiver, $thread);
                        $messageId = \sprintf(
                            'forum-notification-%d%s',
                            $scheduled->getId(),
                            strrchr($sender->getAddress(), '@')
                        );
                        $welcomeMembership = $this->getWelcomeMembership($scheduled);
                        $success = $this->mailer->sendNotificationEmail(
                            $sender,
                            $receiver,
                            [
                                'subject' => $subject,
                                'notification' => $scheduled,
                                'datesent' => $scheduled->getCreated(),
                                'messageId' => $messageId,
                                'previousMessageIds' => $previousMessageIds,
                                'agoraUnsubscribeUrl' => null === $welcomeMembership ? null : $this->uriSigner->sign(
                                    $this->urlGenerator->generate(
                                        'agora_notifications_off',
                                        ['memberId' => $receiver->getId()],
                                        UrlGeneratorInterface::ABSOLUTE_URL,
                                    ),
                                    // a link in a mail: it may be clicked much later
                                    new DateInterval('P1Y'),
                                ),
                            ]
                        );
                        $this->sendBrowserNotification($scheduled);
                        if ($success) {
                            $welcomeMembership?->setWelcomeNoticeSent(true);
                            $notificationStatus = NotificationStatusType::SENT;
                            $scheduled->setMessageId($messageId);
                            $notificationReferences[$referenceKey][] = $messageId;
                            ++$sent;
                        }
                    } catch (Exception $e) {
                        $io->error($e->getMessage());
                    }
                }
                $scheduled->setStatus($notificationStatus);
                $this->entityManager->persist($scheduled);
            }
            $this->entityManager->flush();
            $io->success(
                \sprintf(
                    'Sent %d messages, skipped %d messages',
                    $sent,
                    \count($scheduledNotifications) - $sent
                )
            );
        } else {
            $io->success('No messages to be sent');
        }

        try {
            $this->browserPushNotificationProcessor->process((int) $batchSize);
        } catch (Throwable $throwable) {
            $this->logger->warning('Browser push notification queue processing failed.', [
                'exception' => $throwable,
            ]);
        }

        return 0;
    }

    /**
     * The membership in the BeWelcome group a notification comes from, if the member hasn't been told yet that
     * new members get the agora's posts and how to stop them (the first agora mail tells them).
     */
    private function getWelcomeMembership(PostNotification $notification): ?GroupMembership
    {
        if (
            ForumModel::SUBSCRIPTION_GROUP !== $notification->getTableSubscription()
            || !$notification->getPost()->getThread()->isAgora()
        ) {
            return null;
        }

        $membership = $this->entityManager->getRepository(GroupMembership::class)->findOneBy([
            'group' => Group::BEWELCOME,
            'member' => $notification->getReceiver(),
        ]);

        return null === $membership || $membership->isWelcomeNoticeSent() ? null : $membership;
    }

    private function sendBrowserNotification(PostNotification $notification): void
    {
        if ('members_threads_subscribed' !== $notification->getTableSubscription()) {
            return;
        }

        $post = $notification->getPost();
        $this->browserNotificationService->queue(
            $notification->getReceiver(),
            BrowserNotificationPayload::forum($post->getAuthor(), $this->getForumPostUrl($post))
        );
    }

    private function determineSender(ForumPost $post): Address
    {
        $thread = $post->getThread();
        if (!$thread->isAgora()) {
            $from = new Address('group@bewelcome.org', 'BeWelcome - ' . $post->getAuthor()->getUsername());
        } else {
            $from = new Address('forum@bewelcome.org', 'BeWelcome - ' . $post->getAuthor()->getUsername());
        }

        return $from;
    }

    private function getForumPostUrl(ForumPost $post): string
    {
        $thread = $post->getThread();
        if (null === $thread) {
            return '/forums';
        }

        $fragment = 'post' . $post->getId();
        $group = $thread->getGroup();
        if (!$thread->isAgora()) {
            return $this->urlGenerator->generate('group_forum_thread', [
                'group_id' => $group->getId(),
                'thread' => $thread->getId(),
                '_fragment' => $fragment,
            ]);
        }

        return $this->urlGenerator->generate('forum_thread', [
            'threadId' => $thread->getId(),
            '_fragment' => $fragment,
        ]);
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
        $thread = $notification->getPost()->getThread();
        if (!$thread->isAgora()) {
            $subject .= ' [' . $thread->getGroup()->getName() . ']';
        }

        return strip_tags($subject);
    }
}
