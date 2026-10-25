<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * One choice of a poll. Its text is translatable (PollChoiceTranslation). `votes` counts the votes for it,
 * also in anonymous polls, which do not record who chose what.
 */
#[ORM\Entity]
#[Gedmo\TranslationEntity(class: PollChoiceTranslation::class)]
class PollChoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'choices')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Poll $poll;

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT)]
    private string $text = '';

    #[ORM\Column]
    private int $votes = 0;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, PollChoiceTranslation>
     */
    #[ORM\OneToMany(targetEntity: PollChoiceTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    private Collection $translations;

    #[Gedmo\Locale]
    private ?string $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function setPoll(Poll $poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getVotes(): int
    {
        return $this->votes;
    }

    public function setVotes(int $votes): self
    {
        $this->votes = max(0, $votes);

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * The text in every language it was written in, by locale.
     *
     * @return array<string, string>
     */
    public function getTexts(): array
    {
        $texts = [];
        foreach ($this->translations as $translation) {
            $texts[$translation->getLocale()] = $translation->getContent();
        }

        return [] === $texts && '' !== $this->text ? ['en' => $this->text] : $texts;
    }

    /**
     * @return Collection<int, PollChoiceTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PollChoiceTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setObject($this);
        }

        return $this;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
