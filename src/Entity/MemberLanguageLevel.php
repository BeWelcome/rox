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
#[ORM\Entity]
class MemberLanguageLevel
{
    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'languageLevels')]
    #[ORM\Id]
    protected ?Member $member = null;

    #[ORM\JoinColumn(name: 'language', referencedColumnName: 'ShortCode', nullable: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Language::class)]
    private ?Language $language;

    #[ORM\Column(name: 'level', type: 'language_level', nullable: false)]
    #[ORM\Id]
    private ?string $level = LanguageLevelType::BEGINNER;

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }
}
