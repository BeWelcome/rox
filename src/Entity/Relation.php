<?php

namespace App\Entity;

use App\Repository\RelationRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'relation')]
#[ORM\Index(name: 'owner', columns: ['owner_id'])]
#[ORM\UniqueConstraint(name: 'UniqueRelation', columns: ['owner_id', 'relation_id'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: RelationRepository::class)]
class Relation
{
    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    private ?string $comment;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private DateTime $updated;

    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $owner;

    #[ORM\JoinColumn(name: 'relation_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'relations')]
    private Member $receiver;

    #[ORM\Column(name: 'confirmed', type: 'string', nullable: false)]
    private string $confirmed = 'No';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function setOwner(Member $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner(): Member
    {
        return $this->owner;
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

    public function setConfirmed(string $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getConfirmed(): string
    {
        return $this->confirmed;
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args)
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
    }
}
