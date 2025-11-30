<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\LanguageLevelType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'member_language_level')]
#[ORM\Index(name: 'members_languages', columns: ['member_id', 'language_id'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MembersLanguagesLevel
{
    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'languageLevels')]
    protected Member $member;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\JoinColumn(name: 'language_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Language::class)]
    private Language $language;

    #[ORM\Column(name: 'Level', type: 'language_level', nullable: false)]
    private string $level = LanguageLevelType::BEGINNER;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function setUpdated(?DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?Carbon
    {
        if (null === $this->updated) {
            return null;
        }

        return Carbon::instance($this->updated);
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = Carbon::now();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = Carbon::now();
    }
}
