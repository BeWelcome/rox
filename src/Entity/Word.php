<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\TranslationAllowedType;
use App\Doctrine\TranslationFormat;
use App\Repository\WordRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Word.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'word')]
#[ORM\Entity(repositoryClass: WordRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Word
{
    #[ORM\Id]
    #[ORM\Column(name: 'code', type: 'string', length: 128, nullable: false)]
    private string $code;

    #[ORM\Id]
    #[ORM\JoinColumn(name: 'ShortCode', referencedColumnName: 'ShortCode', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Language::class)]
    private Language $language;

    #[ORM\Column(name: 'domain', type: 'domain', length: 16, nullable: false)]
    private string $domain;

    #[ORM\Column(name: 'Sentence', type: 'text', length: 65535, nullable: false)]
    private string $sentence = '';

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'majorupdate', type: 'datetime', nullable: true)]
    private ?DateTime $majorUpdate = null;

    #[ORM\Column(name: 'donottranslate', type: 'translation_allowed', nullable: false)]
    private string $translationAllowed = TranslationAllowedType::TRANSLATION_ALLOWED;

    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $author;

    #[ORM\Column(name: 'Description', type: 'text', length: 65535, nullable: false)]
    private string $description;

    #[ORM\Column(name: 'TranslationPriority', type: 'integer', nullable: false)]
    private int $translationPriority = 5;

    #[ORM\Column(name: 'isarchived', type: 'boolean', nullable: true)]
    private ?bool $isArchived = null;

    /** On the English text: its text before the last change, and when that was (setSentence()) */
    #[ORM\Column(name: 'previous_sentence', type: 'text', length: 65535, nullable: true)]
    private ?string $previousSentence = null;

    #[ORM\Column(name: 'sentence_changed', type: 'datetime', nullable: true)]
    private ?DateTime $sentenceChanged = null;

    /** On the English text: how it is used on the page; null: detected from the text (getEffectiveFormat()) */
    #[ORM\Column(name: 'format', type: 'string', length: 6, nullable: true, enumType: TranslationFormat::class)]
    private ?TranslationFormat $format = null;

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
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

    /**
     * A change of an English text keeps the text it had (getPreviousSentence()), so that translators see what changed.
     */
    public function setSentence(string $sentence): self
    {
        if (
            '' !== $this->sentence
            && $sentence !== $this->sentence
            && isset($this->language)
            && 'en' === $this->language->getShortCode()
        ) {
            $this->previousSentence = $this->sentence;
            $this->sentenceChanged = new DateTime();
        }
        $this->sentence = $sentence;

        return $this;
    }

    /**
     * On an English text: the text before its last change, null if it never changed.
     */
    public function getPreviousSentence(): ?string
    {
        return $this->previousSentence;
    }

    public function getSentenceChanged(): ?Carbon
    {
        return null === $this->sentenceChanged ? null : Carbon::instance($this->sentenceChanged);
    }

    /**
     * Whether this translation was last written before the last change of its English text, which it may not follow
     * yet (the English text's change is getPreviousSentence() to getSentence()).
     */
    public function isOlderThanTheChangeOf(self $original): bool
    {
        $written = $this->updated ?? $this->created ?? null;

        return null !== $original->sentenceChanged && null !== $original->previousSentence
            && (null === $written || $written < $original->sentenceChanged);
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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setAuthor(?Member $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthor(): ?Member
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

    public function setFormat(?TranslationFormat $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getFormat(): ?TranslationFormat
    {
        return $this->format;
    }

    /**
     * The format set on this text, or the one its text shows.
     */
    public function getEffectiveFormat(): TranslationFormat
    {
        return $this->format ?? TranslationFormat::detect($this->sentence);
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
