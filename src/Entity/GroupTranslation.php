<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * The description of a group in one language (Gedmo personal translation, as ForumThreadTranslation). It belongs
 * to the group, not to the member who wrote it, so the group's admins and translators can change it.
 */
#[ORM\Table(name: 'group_translation')]
#[ORM\UniqueConstraint(name: 'lookup_unique_idx', columns: ['locale', 'object_id', 'field'])]
#[ORM\Entity]
class GroupTranslation extends AbstractPersonalTranslation
{
    public const string DESCRIPTION = 'description';

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;

    /** Who wrote or last changed the text in this language. */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Member $translator = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
        $this->createdAt = new DateTimeImmutable();
    }

    public function getTranslator(): ?Member
    {
        return $this->translator;
    }

    public function setTranslator(?Member $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
