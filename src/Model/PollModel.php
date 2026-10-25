<?php

namespace App\Model;

use App\Dto\PollDto;
use App\Entity\Member;
use App\Entity\Poll;
use App\Entity\PollChoice;
use App\Entity\PollVote;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Creating and editing polls, voting and results.
 *
 * Texts are written in the default language (English), as the legacy pages did ("Use English language for
 * now only"); other languages of old polls stay in poll_translation and are shown to members using them.
 */
class PollModel
{
    public const string DEFAULT_LOCALE = 'en';

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(PollDto $data, Member $creator): Poll
    {
        $poll = new Poll();
        $poll->setTranslatableLocale(self::DEFAULT_LOCALE);
        $poll->setCreator($creator);
        $data->status = Poll::STATUS_DRAFT;
        $data->toEntity($poll);
        foreach ($data->groups as $group) {
            $poll->addGroup($group);
        }

        $this->entityManager->persist($poll);
        $this->entityManager->flush();

        return $poll;
    }

    /**
     * Loads the poll's texts in the default language, for the edit form.
     */
    public function loadForEditing(Poll $poll): void
    {
        $poll->setTranslatableLocale(self::DEFAULT_LOCALE);
        $this->entityManager->refresh($poll);
        foreach ($poll->getChoices() as $choice) {
            $choice->setTranslatableLocale(self::DEFAULT_LOCALE);
            $this->entityManager->refresh($choice);
        }
    }

    public function update(Poll $poll, PollDto $data): void
    {
        $opened = Poll::STATUS_OPEN !== $poll->getStatus() && Poll::STATUS_OPEN === $data->status;
        $poll->setTranslatableLocale(self::DEFAULT_LOCALE);
        $data->toEntity($poll);
        if ($opened) {
            $poll->setStartsAt(new DateTimeImmutable());
        }
        $this->entityManager->flush();
    }

    public function addChoice(Poll $poll, string $text): PollChoice
    {
        $choice = new PollChoice();
        $choice->setTranslatableLocale(self::DEFAULT_LOCALE);
        $choice->setText($text);
        $poll->addChoice($choice);
        $this->entityManager->persist($choice);
        $this->entityManager->flush();

        return $choice;
    }

    public function updateChoice(PollChoice $choice, string $text): void
    {
        $choice->setTranslatableLocale(self::DEFAULT_LOCALE);
        $choice->setText($text);
        $this->entityManager->flush();
    }

    /**
     * Records the vote with the comment, counts it on each chosen choice and, unless the poll is
     * anonymous, keeps which choices the member took.
     *
     * @param PollChoice[] $choices
     */
    public function vote(Poll $poll, Member $member, array $choices, string $comment): void
    {
        $vote = new PollVote();
        $vote->setMember($member);
        $vote->setComment($poll->allowsComments() ? $comment : null);
        $poll->addVote($vote);

        foreach ($choices as $choice) {
            if ($choice->getPoll() !== $poll) {
                continue;
            }
            $choice->setVotes($choice->getVotes() + 1);
            if (!$poll->isAnonymous()) {
                $vote->addChoice($choice);
            }
        }

        $this->entityManager->persist($vote);
        $this->entityManager->flush();
    }

    /**
     * Takes the member's vote back (only for polls that are not anonymous, see PollVoter).
     */
    public function cancelVote(Poll $poll, Member $member): void
    {
        $vote = $poll->getVoteOf($member);
        if (null === $vote) {
            return;
        }
        foreach ($vote->getChoices() as $choice) {
            $choice->setVotes($choice->getVotes() - 1);
        }
        $poll->removeVote($vote);
        $this->entityManager->remove($vote);
        $this->entityManager->flush();
    }

    /**
     * The choices with their votes and share of the voters, most votes first, and the voters' comments.
     *
     * @return array{voters: int, choices: list<array{choice: PollChoice, votes: int, percent: float}>, comments: list<PollVote>}
     */
    public function getResults(Poll $poll): array
    {
        $voters = $poll->getVotes()->count();
        $choices = [];
        foreach ($poll->getChoices() as $choice) {
            $choices[] = [
                'choice' => $choice,
                'votes' => $choice->getVotes(),
                'percent' => $voters > 0 ? round($choice->getVotes() * 100 / $voters, 1) : 0.0,
            ];
        }
        usort($choices, static fn (array $a, array $b) => $b['votes'] <=> $a['votes']);

        $comments = [];
        if ($poll->allowsComments()) {
            foreach ($poll->getVotes() as $vote) {
                if ('' !== trim((string) $vote->getComment())) {
                    $comments[] = $vote;
                }
            }
        }

        return ['voters' => $voters, 'choices' => $choices, 'comments' => $comments];
    }

    public function hasVoted(Poll $poll, Member $member): bool
    {
        return null !== $poll->getVoteOf($member);
    }
}
