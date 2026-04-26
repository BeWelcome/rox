<?php

namespace App\Dto;

use App\Entity\Member;

readonly class MemberSearchResult
{
    public readonly int $messageCount;

    public function __construct(
        public readonly Member $member,
        int $messageSentCount,
        int $messageReceivedCount,
        public readonly int $commentCount,
    ) {
        $this->messageCount = $messageSentCount + $messageReceivedCount;
    }
}
