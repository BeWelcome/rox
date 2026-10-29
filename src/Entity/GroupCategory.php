<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A category groups can be put in (a group can be in several). The group admin team keeps the list; the name is
 * entered per language.
 */
#[ORM\Table(name: 'group_category')]
#[ORM\Entity(repositoryClass: \App\Repository\GroupCategoryRepository::class)]
class GroupCategory
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'created', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $created;

    /** @var Collection<int, GroupCategoryTranslation> the name in every language it was entered in */
    #[ORM\OneToMany(targetEntity: GroupCategoryTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    public function __construct()
    {
        $this->created = new DateTimeImmutable();
        $this->translations = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * Sets the name in a language; an empty name removes that language.
     */
    public function setName(string $locale, string $name): self
    {
        $translation = $this->getNames()[$locale] ?? null;
        $name = trim($name);
        if ('' === $name) {
            if (null !== $translation) {
                $this->translations->removeElement($translation);
            }

            return $this;
        }

        if (null === $translation) {
            $translation = new GroupCategoryTranslation($locale, GroupCategoryTranslation::NAME, $name);
            $translation->setObject($this);
            $this->translations->add($translation);
        } else {
            $translation->setContent($name);
        }

        return $this;
    }

    /**
     * @return array<string, GroupCategoryTranslation> the name in every language, by locale
     */
    public function getNames(): array
    {
        $names = [];
        foreach ($this->translations as $translation) {
            if (GroupCategoryTranslation::NAME === $translation->getField()) {
                $names[$translation->getLocale()] = $translation;
            }
        }

        return $names;
    }

    /**
     * The name in a language; else in English, else in any language there is.
     */
    public function getName(string $locale = 'en'): string
    {
        $names = $this->getNames();
        $name = $names[$locale] ?? $names['en'] ?? reset($names);

        return false === $name ? '' : (string) $name->getContent();
    }
}
