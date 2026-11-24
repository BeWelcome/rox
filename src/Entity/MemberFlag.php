<?php

namespace App\Entity;

use App\Repository\MemberFlagRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A member flagged for the safety team: their username or e-mail address matched a sign-up pattern (or a volunteer
 * flagged them). Until a volunteer releases them, the member is shadow banned: their messages and requests are held
 * back without them noticing (ShadowBanModel). Released, the flag stays as a record.
 */
#[ORM\Table(name: 'member_flag')]
#[ORM\UniqueConstraint(name: 'member_flag_member', columns: ['member_id'])]
#[ORM\Index(name: 'member_flag_released_idx', columns: ['released'])]
#[ORM\Entity(repositoryClass: MemberFlagRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MemberFlag
{
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\JoinColumn(name: 'pattern_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: SignupPattern::class)]
    private ?SignupPattern $pattern;

    /** what matched, e.g. "username janev12" */
    #[ORM\Column(name: 'matched', type: 'string', length: 255, nullable: false)]
    private string $matched;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'released', type: 'datetime', nullable: true)]
    private ?DateTime $released = null;

    #[ORM\JoinColumn(name: 'released_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $releasedBy = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function __construct(Member $member, ?SignupPattern $pattern, string $matched)
    {
        $this->member = $member;
        $this->pattern = $pattern;
        $this->matched = mb_substr($matched, 0, 255);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getPattern(): ?SignupPattern
    {
        return $this->pattern;
    }

    public function getMatched(): string
    {
        return $this->matched;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function isReleased(): bool
    {
        return null !== $this->released;
    }

    public function getReleased(): ?DateTime
    {
        return $this->released;
    }

    public function getReleasedBy(): ?Member
    {
        return $this->releasedBy;
    }

    public function release(Member $volunteer): self
    {
        $this->released = new DateTime();
        $this->releasedBy = $volunteer;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }
}
