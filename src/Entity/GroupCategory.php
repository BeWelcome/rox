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
    public const string DEFAULT_ICON = 'folder';
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'created', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $created;

    /** A Font Awesome name without the prefix (person-hiking), one of the group_category_icons; null: the folder. */
    #[ORM\Column(name: 'icon', type: 'string', length: 40, nullable: true)]
    private ?string $icon = null;

    /** @var list<string> lower-cased words: a group whose name or description has one gets the category suggested */
    #[ORM\Column(name: 'keywords', type: 'json', nullable: true)]
    private ?array $keywords = null;

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

    public function getIcon(): string
    {
        return $this->icon ?? self::DEFAULT_ICON;
    }

    public function hasIcon(): bool
    {
        return null !== $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = '' === (string) $icon ? null : $icon;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getKeywords(): array
    {
        return $this->keywords ?? [];
    }

    /**
     * Keeps each word once, trimmed and lower-cased; empty ones are left out.
     *
     * @param iterable<string> $keywords
     */
    public function setKeywords(iterable $keywords): self
    {
        $words = [];
        foreach ($keywords as $keyword) {
            $word = mb_strtolower(trim((string) $keyword));
            if ('' !== $word && !\in_array($word, $words, true)) {
                $words[] = $word;
            }
        }
        $this->keywords = [] === $words ? null : $words;

        return $this;
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
