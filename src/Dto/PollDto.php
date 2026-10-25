<?php

namespace App\Dto;

use App\Entity\Group;
use App\Entity\Poll;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The data of the create and edit poll form (App\Form\PollType).
 */
class PollDto
{
    #[Assert\NotBlank(message: 'poll.title.empty')]
    #[Assert\Length(max: 255, maxMessage: 'poll.title.too.long')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'poll.description.empty')]
    public ?string $description = null;

    /**
     * Groups whose members may vote (chosen when the poll is created, as on the legacy pages).
     *
     * @var Group[]
     */
    public array $groups = [];

    #[Assert\Choice(choices: [Poll::STATUS_DRAFT, Poll::STATUS_OPEN, Poll::STATUS_CLOSED])]
    public string $status = Poll::STATUS_DRAFT;

    #[Assert\Choice(choices: [Poll::CHOICE_SINGLE, Poll::CHOICE_MULTIPLE, Poll::CHOICE_ORDERED])]
    public string $typeOfChoice = Poll::CHOICE_SINGLE;

    #[Assert\NotNull(message: 'poll.end.empty')]
    public ?DateTimeInterface $ended = null;

    #[Assert\Choice(choices: [Poll::RESULTS_VISIBLE, Poll::RESULTS_AFTER_VOTE, Poll::RESULTS_HIDDEN])]
    public string $resultsVisibility = Poll::RESULTS_AFTER_VOTE;

    public bool $allowComment = false;

    public bool $anonymous = true;

    public bool $canChangeVote = false;

    public function __construct()
    {
        $this->ended = new DateTime('+1 month 23:59');
    }

    public static function fromEntity(Poll $poll): self
    {
        $dto = new self();
        $dto->title = $poll->getTitle();
        $dto->description = $poll->getDescription();
        $dto->groups = $poll->getGroups()->toArray();
        $dto->status = $poll->getStatus();
        $dto->typeOfChoice = $poll->getChoiceType();
        $dto->ended = $poll->getEndsAt() ?? new DateTime('+1 month 23:59');
        $dto->resultsVisibility = $poll->getResultsVisibility();
        $dto->allowComment = $poll->allowsComments();
        $dto->anonymous = $poll->isAnonymous();
        $dto->canChangeVote = $poll->canChangeVote();

        return $dto;
    }

    public function toEntity(Poll $poll): void
    {
        $poll
            ->setTitle((string) $this->title)
            ->setDescription((string) $this->description)
            ->setStatus($this->status)
            ->setChoiceType($this->typeOfChoice)
            ->setEndsAt(null === $this->ended ? null : DateTimeImmutable::createFromInterface($this->ended))
            ->setResultsVisibility($this->resultsVisibility)
            ->setAllowComments($this->allowComment)
            ->setAnonymous($this->anonymous)
            ->setCanChangeVote($this->canChangeVote)
        ;
    }
}
