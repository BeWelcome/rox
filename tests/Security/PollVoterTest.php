<?php

namespace App\Tests\Security;

use App\Entity\Member;
use App\Entity\Poll;
use App\Entity\PollVote;
use App\Security\PollVoter;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PollVoterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public static function votes(): array
    {
        $granted = VoterInterface::ACCESS_GRANTED;
        $denied = VoterInterface::ACCESS_DENIED;

        // attribute, status, creator?, admin?, voted?, options, expected
        return [
            'others see an open poll' => [PollVoter::VIEW, Poll::STATUS_OPEN, false, false, false, [], $granted],
            'others do not see a draft' => [PollVoter::VIEW, Poll::STATUS_DRAFT, false, false, false, [], $denied],
            'the creator sees the draft' => [PollVoter::VIEW, Poll::STATUS_DRAFT, true, false, false, [], $granted],
            'admins see drafts' => [PollVoter::VIEW, Poll::STATUS_DRAFT, false, true, false, [], $granted],
            'the creator edits the draft' => [PollVoter::EDIT, Poll::STATUS_DRAFT, true, false, false, [], $granted],
            'the creator does not edit an open poll' => [PollVoter::EDIT, Poll::STATUS_OPEN, true, false, false, [], $denied],
            'admins edit open polls' => [PollVoter::EDIT, Poll::STATUS_OPEN, false, true, false, [], $granted],
            'members vote in open polls' => [PollVoter::VOTE, Poll::STATUS_OPEN, false, false, false, [], $granted],
            'members vote once' => [PollVoter::VOTE, Poll::STATUS_OPEN, false, false, true, [], $denied],
            'nobody votes in closed polls' => [PollVoter::VOTE, Poll::STATUS_CLOSED, false, false, false, [], $denied],
            'nobody votes past the end' => [PollVoter::VOTE, Poll::STATUS_OPEN, false, false, false, ['ended' => '-1 day'], $denied],
            'nobody votes in ordered polls' => [PollVoter::VOTE, Poll::STATUS_OPEN, false, false, false, ['type' => Poll::CHOICE_ORDERED], $denied],
            'a vote is taken back if allowed' => [PollVoter::CANCEL_VOTE, Poll::STATUS_OPEN, false, false, true, ['change' => true, 'anonym' => false], $granted],
            'anonymous votes are not taken back' => [PollVoter::CANCEL_VOTE, Poll::STATUS_OPEN, false, false, true, ['change' => true, 'anonym' => true], $denied],
            'results after the vote' => [PollVoter::RESULTS, Poll::STATUS_OPEN, false, false, true, [], $granted],
            'no results before the vote' => [PollVoter::RESULTS, Poll::STATUS_OPEN, false, false, false, [], $denied],
            'results once closed' => [PollVoter::RESULTS, Poll::STATUS_CLOSED, false, false, false, [], $granted],
            'hidden results' => [PollVoter::RESULTS, Poll::STATUS_CLOSED, false, false, true, ['results' => Poll::RESULTS_HIDDEN], $denied],
            'hidden results for admins' => [PollVoter::RESULTS, Poll::STATUS_CLOSED, false, true, false, ['results' => Poll::RESULTS_HIDDEN], $granted],
        ];
    }

    #[DataProvider('votes')]
    public function testVote(string $attribute, string $status, bool $isCreator, bool $isAdmin, bool $voted, array $options, int $expected): void
    {
        $member = Mockery::mock(Member::class);
        $member->allows('getGroups')->andReturn([]);
        $token = Mockery::mock(TokenInterface::class, ['getUser' => $member]);
        $decisionManager = Mockery::mock(AccessDecisionManagerInterface::class);
        $decisionManager->allows('decide')->andReturn($isAdmin);

        $poll = new Poll();
        $poll->setStatus($status);
        $poll->setCreator($isCreator ? $member : Mockery::mock(Member::class));
        $poll->setEndsAt(new DateTimeImmutable($options['ended'] ?? '+1 week'));
        $poll->setChoiceType($options['type'] ?? Poll::CHOICE_SINGLE);
        $poll->setCanChangeVote($options['change'] ?? false);
        $poll->setAnonymous($options['anonym'] ?? true);
        $poll->setResultsVisibility($options['results'] ?? Poll::RESULTS_AFTER_VOTE);
        if ($voted) {
            $vote = new PollVote();
            $vote->setMember($member);
            $poll->addVote($vote);
        }

        self::assertSame($expected, new PollVoter($decisionManager)->vote($token, $poll, [$attribute]));
    }
}
