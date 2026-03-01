<?php

namespace App\Entity;

use App\Repository\FriendRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'friend')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FriendRepository::class)]
class Friend
{
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated;

    #[ORM\Column(name: 'left_confirmed', type: Types::BOOLEAN, nullable: false)]
    private bool $leftConfirmed = false;

    #[ORM\JoinColumn(name: 'left_id', referencedColumnName: 'id')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $left;

    #[ORM\JoinColumn(name: 'right_id', referencedColumnName: 'id')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $right;

    #[ORM\Column(name: 'right_confirmed', type: Types::BOOLEAN, nullable: false)]
    private bool $rightConfirmed = false;

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getUpdated(): ?Carbon
    {
        if (null === $this->updated) {
            return null;
        }

        return Carbon::instance($this->updated);
    }

    public function setLeft(Member $left): self
    {
        $this->left = $left;

        return $this;
    }

    public function getLeft(): Member
    {
        return $this->left;
    }

    public function setRight(Member $right): self
    {
        $this->right = $right;

        return $this;
    }

    public function getRight(): Member
    {
        return $this->right;
    }

    public function setLeftConfirmed(bool $leftConfirmed): self
    {
        $this->leftConfirmed = $leftConfirmed;

        return $this;
    }

    public function getLeftConfirmed(): bool
    {
        return $this->leftConfirmed;
    }

    public function setRightConfirmed(bool $rightConfirmed): self
    {
        $this->rightConfirmed = $rightConfirmed;

        return $this;
    }

    public function getRightConfirmed(): bool
    {
        return $this->rightConfirmed;
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
