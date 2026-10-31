<?php

namespace App\Entity;

use App\Repository\FriendRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * A friendship between two members. One of them asks (the requester), the other one confirms. Until then it is
 * pending and only shown to the two of them; once confirmed it shows on both profiles.
 *
 * Left is always the member with the lower id, so a pair has one row only.
 *
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'friend')]
#[ORM\Index(name: 'idx_friend_requester', columns: ['requester_id'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FriendRepository::class)]
class Friend
{
    #[ORM\Column(name: 'created', type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\JoinColumn(name: 'left_id', referencedColumnName: 'id')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $left;

    #[ORM\JoinColumn(name: 'right_id', referencedColumnName: 'id')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $right;

    #[ORM\JoinColumn(name: 'requester_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $requester;

    #[ORM\Column(name: 'confirmed', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $confirmed = null;

    /**
     * A pending friendship that $requester asks $receiver to confirm.
     */
    public static function request(Member $requester, Member $receiver): self
    {
        if ($requester->getId() === $receiver->getId()) {
            throw new InvalidArgumentException('A member can\'t be their own friend.');
        }

        $friend = new self();
        $friend->requester = $requester;
        $friend->left = $requester->getId() < $receiver->getId() ? $requester : $receiver;
        $friend->right = $requester->getId() < $receiver->getId() ? $receiver : $requester;

        return $friend;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getUpdated(): ?Carbon
    {
        return null === $this->updated ? null : Carbon::instance($this->updated);
    }

    public function getLeft(): Member
    {
        return $this->left;
    }

    public function getRight(): Member
    {
        return $this->right;
    }

    public function getRequester(): Member
    {
        return $this->requester;
    }

    /**
     * The member asked to confirm.
     */
    public function getReceiver(): Member
    {
        return $this->getOther($this->requester);
    }

    /**
     * The friend of $member: the other side of the friendship.
     */
    public function getOther(Member $member): Member
    {
        return $this->left->getId() === $member->getId() ? $this->right : $this->left;
    }

    public function isRequester(Member $member): bool
    {
        return $this->requester->getId() === $member->getId();
    }

    public function isConfirmed(): bool
    {
        return null !== $this->confirmed;
    }

    public function getConfirmed(): ?Carbon
    {
        return null === $this->confirmed ? null : Carbon::instance($this->confirmed);
    }

    public function confirm(): self
    {
        $this->confirmed ??= new DateTime();

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void
    {
        $this->created = new DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(PreUpdateEventArgs $args): void
    {
        $this->updated = new DateTime();
    }
}
