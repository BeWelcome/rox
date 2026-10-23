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
        public readonly int $positiveCount = 0,
        public readonly int $neutralCount = 0,
        public readonly int $negativeCount = 0,
    ) {
        $this->messageCount = $messageSentCount + $messageReceivedCount;
    }
}
