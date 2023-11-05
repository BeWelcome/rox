<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * MemberTranslation.
 *
 * @ORM\Table(name="memberstrads",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="Unique_entry", columns={"IdTrad", "IdOwner", "IdLanguage"})},
 *     indexes={
 *         @ORM\Index(name="memberstrads_trads", columns={"IdTrad"}),
 *         @ORM\Index(name="memberstrads_language", columns={"IdLanguage"}),
 *         @ORM\Index(name="memberstrads_trad_language", columns={"IdLanguage", "IdTrad"})
 *      }
 *     )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class MemberTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Member", fetch="EAGER")
     * @ORM\JoinColumn(name="IdOwner", referencedColumnName="id")
     */
    private Member $owner;

    /**
     * @ORM\Column(name="IdTrad", type="integer", nullable=false)
     */
    private int $translation;

    /**
     * @ORM\ManyToOne(targetEntity="Member", fetch="LAZY")
     * @ORM\JoinColumn(name="IdTranslator", nullable=false)
     */
    private Member $translator;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private DateTime $updated;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private string $type = 'member';

    /**
     * @ORM\Column(name="Sentence", type="text", length=65535, nullable=false)
     */
    private string $sentence;

    /**
     * @ORM\Column(name="IdRecord", type="integer", nullable=false)
     */
    private int $record = -1;

    /**
     * @ORM\Column(name="TableColumn", type="string", length=200, nullable=false)
     */
    private string $tableColumn = 'NotSet';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     *   @ORM\JoinColumn(name="IdLanguage", referencedColumnName="id")
     */
    private Language $language;

    public function setOwner(Member $owner):self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner():Member
    {
        return $this->owner;
    }

    public function setTranslation(int $translation): self
    {
        $this->translation = $translation;

        return $this;
    }

    public function getTranslation(): int
    {
        return $this->translation;
    }

    public function setTranslator(Member $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function getTranslator(): Member
    {
        return $this->translator;
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): Carbon
    {
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

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
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

    public function setRecord(int $record): self
    {
        $this->record = $record;

        return $this;
    }

    public function getRecord(): int
    {
        return $this->record;
    }

    public function setTableColumn($tableColumn): self
    {
        $this->tableColumn = $tableColumn;

        return $this;
    }

    public function getTableColumn(): string
    {
        return $this->tableColumn;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setLanguage(?Language $language = null): self
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
        $this->translation = random_int(0, 24500000);
    }

    /**
     * Triggered after insert.
     *
     * @ORM\PostPersist
     */
    public function onPostPersist()
    {
        $this->translation = $this->id;
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
