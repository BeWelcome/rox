<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Override;

/**
 * The message of a forum post in one language (Gedmo personal translation, as MemberTranslation). Every language
 * is here, the one it was written in included; forum_post.message keeps the original.
 */
#[ORM\Table(name: 'forum_post_translation')]
#[ORM\UniqueConstraint(name: 'lookup_unique_idx', columns: ['locale', 'object_id', 'field'])]
#[ORM\Entity]
class ForumPostTranslation extends AbstractPersonalTranslation
{
    #[ORM\ManyToOne(targetEntity: ForumPost::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Override]
    protected $object;

    /** Who wrote or last changed the text in this language (the forum shows "translated by"). */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Member $translator = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $createdAt = null;

    /** Set when the text was changed after it was written (the forum shows it as edited). */
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
