<?php

namespace App\Entity;

use App\Repository\PostNotificationRepository;
use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * PostNotification.
 */
#[ORM\Table(name: 'posts_notificationqueue')]
#[ORM\Index(name: 'posts_notificationqueue_status', columns: ['Status'])]
#[ORM\Entity(repositoryClass: PostNotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PostNotification
{
    use LifecycleCallbacksTrait;

    #[ORM\Column(name: 'Status', type: 'string', nullable: false)]
    private string $status = 'ToSend';

    #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $receiver;

    #[ORM\JoinColumn(name: 'IdPost', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: ForumPost::class)]
    private ForumPost $post;

    #[ORM\Column(name: 'Type', type: 'string', nullable: false)]
    private string $type = 'buggy';

    #[ORM\Column(name: 'IdSubscription', type: 'integer', nullable: false)]
    private int $subscription = 0;

    #[ORM\Column(name: 'TableSubscription', type: 'string', length: 64, nullable: false)]
    private string $tableSubscription = 'NotSet';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

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

    public function getId(): int
    {
        return $this->id;
    }
}
