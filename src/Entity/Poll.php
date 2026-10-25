<?php

namespace App\Entity;

use App\Repository\PollRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * A poll members vote in. Title and description are translatable (PollTranslation); the default language
 * is English. Version20261026100000 filled this table from the legacy polls table (App\Entity\Polls).
 */
#[ORM\Entity(repositoryClass: PollRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\TranslationEntity(class: PollTranslation::class)]
class Poll
{
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_OPEN = 'open';
    public const string STATUS_CLOSED = 'closed';

    public const string CHOICE_SINGLE = 'single';
    public const string CHOICE_MULTIPLE = 'multiple';
    // Only on a few old polls; voting on them was never implemented.
    public const string CHOICE_ORDERED = 'ordered';

    public const string RESULTS_AFTER_VOTE = 'after_vote';
    public const string RESULTS_VISIBLE = 'visible';
    public const string RESULTS_HIDDEN = 'hidden';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Member $creator = null;

    #[Gedmo\Translatable]
    #[ORM\Column(length: 255)]
    private string $title = '';

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT, length: 16777215)]
    private string $description = '';

    #[ORM\Column(length: 10)]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(length: 10)]
    private string $choiceType = self::CHOICE_SINGLE;

    #[ORM\Column(length: 12)]
    private string $resultsVisibility = self::RESULTS_AFTER_VOTE;

    #[ORM\Column]
    private bool $anonymous = true;

    #[ORM\Column]
    private bool $allowComments = false;

    #[ORM\Column]
    private bool $canChangeVote = false;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $startsAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $endsAt = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    /**
     * Only members of one of these groups vote; none means every member.
     *
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class)]
    #[ORM\JoinTable(name: 'poll_group')]
    private Collection $groups;

    /**
     * @var Collection<int, PollChoice>
     */
    #[ORM\OneToMany(targetEntity: PollChoice::class, mappedBy: 'poll', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $choices;

    /**
     * @var Collection<int, PollVote>
     */
    #[ORM\OneToMany(targetEntity: PollVote::class, mappedBy: 'poll', cascade: ['remove'])]
    private Collection $votes;

    /**
     * @var Collection<int, PollTranslation>
     */
    #[ORM\OneToMany(targetEntity: PollTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    private Collection $translations;

    #[Gedmo\Locale]
    private ?string $locale = null;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->choices = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreator(): ?Member
    {
        return $this->creator;
    }

    public function setCreator(?Member $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getChoiceType(): string
    {
        return $this->choiceType;
    }

    public function setChoiceType(string $choiceType): self
    {
        $this->choiceType = $choiceType;

        return $this;
    }

    public function getResultsVisibility(): string
    {
        return $this->resultsVisibility;
    }

    public function setResultsVisibility(string $resultsVisibility): self
    {
        $this->resultsVisibility = $resultsVisibility;

        return $this;
    }

    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): self
    {
        $this->anonymous = $anonymous;

        return $this;
    }

    public function allowsComments(): bool
    {
        return $this->allowComments;
    }

    public function setAllowComments(bool $allowComments): self
    {
        $this->allowComments = $allowComments;

        return $this;
    }

    public function canChangeVote(): bool
    {
        return $this->canChangeVote;
    }

    public function setCanChangeVote(bool $canChangeVote): self
    {
        $this->canChangeVote = $canChangeVote;

        return $this;
    }

    public function getStartsAt(): ?DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?DateTimeImmutable $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?DateTimeImmutable $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * @return Collection<int, PollChoice>
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(PollChoice $choice): self
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setPoll($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, PollVote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(PollVote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setPoll($this);
        }

        return $this;
    }

    public function removeVote(PollVote $vote): self
    {
        $this->votes->removeElement($vote);

        return $this;
    }

    public function getVoteOf(Member $member): ?PollVote
    {
        foreach ($this->votes as $vote) {
            if ($vote->getMember() === $member) {
                return $vote;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, PollTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PollTranslation $translation): self
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

    /**
     * The title in every language it was written in, by locale.
     *
     * @return array<string, string>
     */
    public function getTitles(): array
    {
        return $this->getTranslated('title', $this->title);
    }

    /**
     * The description in every language it was written in, by locale.
     *
     * @return array<string, string>
     */
    public function getDescriptions(): array
    {
        return $this->getTranslated('description', $this->description);
    }

    public function isDraft(): bool
    {
        return self::STATUS_DRAFT === $this->status;
    }

    /**
     * Open for votes: open and not past its end.
     */
    public function isOpen(): bool
    {
        return self::STATUS_OPEN === $this->status && !$this->hasEnded();
    }

    /**
     * Closed, or open but past its end.
     */
    public function isClosed(): bool
    {
        return self::STATUS_CLOSED === $this->status || (self::STATUS_OPEN === $this->status && $this->hasEnded());
    }

    public function hasEnded(): bool
    {
        return null !== $this->endsAt && $this->endsAt < new DateTimeImmutable();
    }

    public function isMultipleChoice(): bool
    {
        return self::CHOICE_MULTIPLE === $this->choiceType;
    }

    /**
     * @return array<string, string>
     */
    private function getTranslated(string $field, string $default): array
    {
        $translated = [];
        foreach ($this->translations as $translation) {
            if ($field === $translation->getField()) {
                $translated[$translation->getLocale()] = $translation->getContent();
            }
        }

        return [] === $translated && '' !== $default ? ['en' => $default] : $translated;
    }
}
