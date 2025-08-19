<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\TranslationAllowedType;
use App\Repository\WordRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Word.
 *
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'words')]
#[ORM\UniqueConstraint(name: 'code', columns: ['code', 'IdLanguage'])]
#[ORM\UniqueConstraint(name: 'code_2', columns: ['code', 'ShortCode'])]
#[ORM\Entity(repositoryClass: WordRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Word
{
    #[ORM\Column(name: 'code', type: 'string', length: 128, nullable: false)]
    private string $code;

    #[ORM\Column(name: 'domain', type: 'domain', length: 16, nullable: false)]
    private string $domain;

    #[ORM\Column(name: 'ShortCode', type: 'string', length: 16, nullable: false)]
    private string $shortCode = 'en';

    #[ORM\Column(name: 'Sentence', type: 'text', length: 65535, nullable: false)]
    private string $sentence;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'majorupdate', type: 'datetime', nullable: true)]
    private ?DateTime $majorUpdate  = null;

    #[ORM\Column(name: 'donottranslate', type: 'translation_allowed', nullable: false)]
    private string $translationAllowed = TranslationAllowedType::TRANSLATION_ALLOWED;

    #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $author;

    #[ORM\JoinColumn(name: 'IdLanguage', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Language::class)]
    private Language $language;

    #[ORM\Column(name: 'Description', type: 'text', length: 65535, nullable: false)]
    private string $description;

    #[ORM\Column(name: 'TranslationPriority', type: 'integer', nullable: false)]
    private int $translationPriority = 5;

    #[ORM\Column(name: 'isarchived', type: 'boolean', nullable: true)]
    private ?bool $isArchived = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
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

    public function setSentence(string $sentence): self
    {
        $this->sentence = $sentence;

        return $this;
    }

    public function getSentence(): string
    {
        return $this->sentence;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return new Carbon($this->created);
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): Carbon
    {
        return new Carbon($this->updated);
    }

    public function setMajorUpdate(?DateTime $majorUpdate): self
    {
        $this->majorUpdate = $majorUpdate;

        return $this;
    }

    public function getMajorUpdate(): ?Carbon
    {
        if (null === $this->majorUpdate) {
            return null;
        }

        return new Carbon($this->majorUpdate);
    }

    public function setTranslationAllowed(string $translationAllowed): self
    {
        $this->translationAllowed = $translationAllowed;

        return $this;
    }

    public function getTranslationAllowed(): string
    {
        return $this->translationAllowed;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;
        $this->setShortCode($language->getShortCode());

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setAuthor(Member $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthor(): Member
    {
        return $this->author;
    }

    public function setTranslationPriority(int $translationPriority): self
    {
        $this->translationPriority = $translationPriority;

        return $this;
    }

    public function getTranslationPriority(): int
    {
        return $this->translationPriority;
    }

    public function setIsArchived(?bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new Carbon('now');
        $this->updated = $this->created;
        $this->majorUpdate = $this->created;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new Carbon('now');
    }
}
