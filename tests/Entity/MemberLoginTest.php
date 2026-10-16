<?php

namespace App\Tests\Entity;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Who may log in, as on prod (v3.8.5): everyone whose profile can be browsed. Route C refused ChoiceInactive,
 * NeedMore, ActiveHidden and Pending members (4,360 on prod).
 */
class MemberLoginTest extends TestCase
{
    public static function statuses(): array
    {
        return [
            'Active' => [MemberStatusType::ACTIVE, false],
            'OutOfRemind' => [MemberStatusType::OUT_OF_REMIND, false],
            'ChoiceInactive' => [MemberStatusType::CHOICE_INACTIVE, false],
            'NeedMore' => [MemberStatusType::NEED_MORE, false],
            'ActiveHidden' => [MemberStatusType::ACTIVE_HIDDEN, false],
            'Pending' => [MemberStatusType::PENDING, false],
            'Banned' => [MemberStatusType::BANNED, true],
            'Suspended' => [MemberStatusType::SUSPENDED, true],
            'TakenOut' => [MemberStatusType::TAKEN_OUT, true],
            'AskToLeave' => [MemberStatusType::ASKED_TO_LEAVE, true],
            'PassedAway' => [MemberStatusType::PASSED_AWAY, true],
        ];
    }

    #[DataProvider('statuses')]
    public function testLoginIsRefusedOnlyForProfilesThatCannotBeBrowsed(string $status, bool $denied): void
    {
        $this->assertSame($denied, (new Member())->setStatus($status)->isDeniedAccess());
    }
}
