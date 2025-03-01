<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Translations.
 */
#[ORM\Table(name: 'translations')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class Translation
{
    #[ORM\JoinColumn(name: 'IdLanguage', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Language::class)]
    private Language $language;

    #[ORM\JoinColumn(name: 'IdOwner', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $owner;

    #[ORM\Column(name: 'IdTrad', type: 'integer', nullable: false)]
    private int $idTrad;

    #[ORM\JoinColumn(name: 'IdTranslator', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $translator;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'Type', type: 'string', nullable: false)]
    private string $type;

    #[ORM\Column(name: 'Sentence', type: 'text', length: 65535, nullable: false)]
    private string $sentence;

    #[ORM\Column(name: 'IdRecord', type: 'integer', nullable: false)]
    private int $idrecord;

    #[ORM\Column(name: 'TableColumn', type: 'string', length: 200, nullable: false)]
    private string $tablecolumn = 'NotSet';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
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

    public function setIdTrad(int $idTrad): self
    {
        $this->idTrad = $idTrad;

        return $this;
    }

    public function getIdTrad(): int
    {
        return $this->idTrad;
    }

    public function setTranslator(?Member $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function getTranslator(): ?Member
    {
        return $this->translator;
    }

    public function getUpdated(): ?Carbon
    {
        if (null === $this->updated) {
            return null;
        }

        return Carbon::instance($this->updated);
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

    public function setIdrecord(int $idrecord): self
    {
        $this->idrecord = $idrecord;

        return $this;
    }

    public function getIdrecord(): int
    {
        return $this->idrecord;
    }

    public function setTablecolumn(string $tablecolumn): self
    {
        $this->tablecolumn = $tablecolumn;

        return $this;
    }

    public function getTablecolumn(): string
    {
        return $this->tablecolumn;
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('now');
    }
}
