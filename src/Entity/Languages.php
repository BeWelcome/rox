<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'languages')]
#[ORM\UniqueConstraint(name: 'ShortCode', columns: ['ShortCode'])]
class Languages
{
    #[ORM\Column(name: 'Name', type: 'text', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'ShortCode', type: 'string', length: 16, nullable: false)]
    private string $shortCode;

    #[ORM\Column(name: 'IsWrittenLanguage', type: 'boolean', nullable: false)]
    private bool $isWrittenLanguage = false;

    #[ORM\Column(name: 'IsSpokenLanguage', type: 'boolean', nullable: false)]
    private bool $isSpokenLanguage = false;

    #[ORM\Column(name: 'IsSignLanguage', type: 'boolean', nullable: false)]
    private bool $isSignLanguage = false;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private readonly int $id;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setShortCode(string $shortCode): self
    {
        $this->shortCode = $shortCode;

        return $this;
    }

    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function setIsWrittenLanguage(bool $isWrittenLanguage): self
    {
        $this->isWrittenLanguage = $isWrittenLanguage;

        return $this;
    }

    public function getIsWrittenLanguage(): bool
    {
        return $this->isWrittenLanguage;
    }

    public function setIsSpokenLanguage(bool $isSpokenLanguage): self
    {
        $this->isSpokenLanguage = $isSpokenLanguage;

        return $this;
    }

    public function getIsSpokenLanguage(): bool
    {
        return $this->isSpokenLanguage;
    }

    public function setIsSignLanguage(bool $isSignLanguage): self
    {
        $this->isSignLanguage = $isSignLanguage;

        return $this;
    }

    public function getIsSignLanguage(): bool
    {
        return $this->isSignLanguage;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
