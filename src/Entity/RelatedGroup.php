<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * A group linked to another group as related ("see also"). Unlinking keeps the row and records
 * who removed it (deletedBy), so the related groups log of a group shows both actions.
 *
 * Still written by the legacy related groups code (build/relatedgroups), hence the column names.
 */
#[ORM\Table(name: 'groups_related')]
#[ORM\Entity]
class RelatedGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Group $group;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'related_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Group $relatedGroup;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'addedby', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Member $addedBy;

    // CASCADE, not SET NULL: a removed link is marked by deletedBy, setting it to NULL would
    // bring the link back. The row of a removed link is only a log entry.
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'deletedby', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Member $deletedBy = null;

    /** When the link was added, or removed once deletedBy is set. */
    #[ORM\Column(name: 'ts', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeInterface $updated;

    public function __construct(Group $group, Group $relatedGroup, ?Member $addedBy)
    {
        $this->group = $group;
        $this->relatedGroup = $relatedGroup;
        $this->addedBy = $addedBy;
        $this->updated = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function getRelatedGroup(): Group
    {
        return $this->relatedGroup;
    }

    public function getAddedBy(): ?Member
    {
        return $this->addedBy;
    }

    public function getDeletedBy(): ?Member
    {
        return $this->deletedBy;
    }

    public function getUpdated(): DateTimeInterface
    {
        return $this->updated;
    }

    public function isRemoved(): bool
    {
        return null !== $this->deletedBy;
    }

    public function remove(Member $member): self
    {
        $this->deletedBy = $member;
        $this->updated = new DateTime();

        return $this;
    }
}
