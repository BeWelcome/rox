<?php

namespace App\Entity;

use App\Repository\SignupPatternRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A text the safety team watches for in the usernames or e-mail addresses of new accounts (ShadowBanModel): spammers
 * reuse them, with a number added and the like. An account signing up with it is flagged for the team and shadow
 * banned (its messages and requests are held back without it noticing) until the team decides.
 *
 * The text matches anywhere in the username or address, upper or lower case; * stands for any characters
 * ("jane*v" matches janev, jane.v and jane_doe_v).
 */
#[ORM\Table(name: 'signup_pattern')]
#[ORM\Entity(repositoryClass: SignupPatternRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SignupPattern
{
    public const string FIELD_BOTH = 'both';
    public const string FIELD_USERNAME = 'username';
    public const string FIELD_EMAIL = 'email';
    public const array FIELDS = [self::FIELD_BOTH, self::FIELD_USERNAME, self::FIELD_EMAIL];

    #[ORM\Column(name: 'pattern', type: 'string', length: 100, nullable: false)]
    private string $pattern;

    #[ORM\Column(name: 'field', type: 'string', length: 8, nullable: false)]
    private string $field = self::FIELD_BOTH;

    #[ORM\Column(name: 'note', type: 'string', length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(name: 'active', type: 'boolean', nullable: false)]
    private bool $active = true;

    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $createdBy = null;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = mb_strtolower(trim($pattern));

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = \in_array($field, self::FIELDS, true) ? $field : self::FIELD_BOTH;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = null === $note || '' === trim($note) ? null : trim($note);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCreatedBy(): ?Member
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Member $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }
}
