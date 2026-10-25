<?php

namespace App\Tests\Security;

use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\Member;
use App\Security\ActivityVoter;
use DateTime;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ActivityVoterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public static function votes(): array
    {
        $granted = VoterInterface::ACCESS_GRANTED;
        $denied = VoterInterface::ACCESS_DENIED;

        // attribute, organizer?, ends in the future?, cancelled?, expected
        return [
            'organizer edits' => [ActivityVoter::EDIT, true, true, false, $granted],
            'organizer edits a cancelled activity' => [ActivityVoter::EDIT, true, true, true, $granted],
            'organizer can not edit when over' => [ActivityVoter::EDIT, true, false, false, $denied],
            'other member can not edit' => [ActivityVoter::EDIT, false, true, false, $denied],
            'organizer cancels' => [ActivityVoter::CANCEL, true, true, false, $granted],
            'other member can not cancel' => [ActivityVoter::CANCEL, false, true, false, $denied],
            'organizer can not cancel when over' => [ActivityVoter::CANCEL, true, false, false, $denied],
            'member answers' => [ActivityVoter::ATTEND, false, true, false, $granted],
            'nobody answers a cancelled activity' => [ActivityVoter::ATTEND, false, true, true, $denied],
            'nobody answers when over' => [ActivityVoter::ATTEND, true, false, false, $denied],
        ];
    }

    #[DataProvider('votes')]
    public function testVote(string $attribute, bool $organizer, bool $upcoming, bool $cancelled, int $expected): void
    {
        $member = Mockery::mock(Member::class);
        $token = Mockery::mock(TokenInterface::class, ['getUser' => $member]);

        $activity = new Activity();
        $activity->setStarts(new DateTime($upcoming ? '+1 day' : '-2 days'));
        $activity->setEnds(new DateTime($upcoming ? '+1 day 2 hours' : '-1 day'));
        $activity->setStatus($cancelled ? Activity::STATUS_CANCELLED : Activity::STATUS_ACTIVE);
        $organizerMember = $organizer ? $member : Mockery::mock(Member::class);
        $activity->addAttendee(new ActivityAttendee($activity, $organizerMember, ActivityAttendee::ATTENDS_YES, '', true));

        self::assertSame($expected, new ActivityVoter()->vote($token, $activity, [$attribute]));
    }

    public function testAbstainsForOtherSubjectsAndAttributes(): void
    {
        $token = Mockery::mock(TokenInterface::class, ['getUser' => Mockery::mock(Member::class)]);
        $voter = new ActivityVoter();

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, new Activity(), ['TRIP_EDIT']));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, new stdClass(), [ActivityVoter::EDIT]));
    }

    public function testDeniesWhenNotLoggedIn(): void
    {
        $token = Mockery::mock(TokenInterface::class, ['getUser' => null]);
        $activity = new Activity();
        $activity->setEnds(new DateTime('+1 day'));

        self::assertSame(VoterInterface::ACCESS_DENIED, new ActivityVoter()->vote($token, $activity, [ActivityVoter::ATTEND]));
    }
}
