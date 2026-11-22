<?php

namespace App\Entity;

use App\Doctrine\NotificationStatusType;
use App\Repository\ForumPostNotificationRepository;
use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * A notification about a forum post that waits for the send:notifications cron, or was sent: one per receiver and
 * post (App\Model\ForumModel queues them, App\Command\SendAndPushNotificationsCommand sends them).
 *
 * In a table of its own, `forum_post_notification`, which got the notifications that weren't sent yet, and the sent
 * ones with a Message-ID (what the later mails of a thread refer to), from main's `posts_notificationqueue`
 * (App\Entity\PostNotification). main's table stays as main uses it: its Status knows
 * ToSend, Sent and Failed only, so the 'Freeze' of a notification that isn't sent (the receiver isn't active, the
 * mail failed) was stored as an empty status there.
 */
#[ORM\Table(name: 'forum_post_notification')]
#[ORM\Index(name: 'forum_post_notification_status_idx', columns: ['status', 'created'])]
#[ORM\UniqueConstraint(name: 'forum_post_notification_message_id_unique', columns: ['message_id'])]
#[ORM\Entity(repositoryClass: ForumPostNotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ForumPostNotification
{
    use LifecycleCallbacksTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /** NotificationStatusType: ToSend, Sent or Freeze */
    #[ORM\Column(name: 'status', type: 'string', length: 16, nullable: false)]
    private string $status = NotificationStatusType::SCHEDULED;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'receiver_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Member $receiver;

    #[ORM\ManyToOne(targetEntity: ForumPost::class)]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ForumPost $post;

    /** What happened to the post: newthread, reply, useredit, ... */
    #[ORM\Column(name: 'type', type: 'string', length: 32, nullable: false)]
    private string $type = 'buggy';

    /** The id of the subscription the notification comes from, in $tableSubscription */
    #[ORM\Column(name: 'subscription_id', type: 'integer', nullable: false)]
    private int $subscription = 0;

    #[ORM\Column(name: 'subscription_table', type: 'string', length: 64, nullable: false)]
    private string $tableSubscription = 'NotSet';

    /** The Message-ID of the mail sent, for the References of the thread's later mails */
    #[ORM\Column(name: 'message_id', type: 'string', length: 255, nullable: true)]
    private ?string $messageId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setReceiver(Member $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getReceiver(): Member
    {
        return $this->receiver;
    }

    public function setPost(ForumPost $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getPost(): ForumPost
    {
        return $this->post;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setSubscription(int $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getSubscription(): int
    {
        return $this->subscription;
    }

    public function setTableSubscription(string $tableSubscription): self
    {
        $this->tableSubscription = $tableSubscription;

        return $this;
    }

    public function getTableSubscription(): string
    {
        return $this->tableSubscription;
    }

    public function setMessageId(?string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }
}
