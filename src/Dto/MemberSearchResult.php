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
        // Share of hosting requests answered in the last 12 months, null when too few (ReplyRate::getShownPercentage())
        public readonly ?int $replyRate = null,
    ) {
        $this->messageCount = $messageSentCount + $messageReceivedCount;
    }

    public function withReplyRate(?int $replyRate): self
    {
        return new self(
            $this->member,
            $this->messageCount,
            0,
            $this->commentCount,
            $this->positiveCount,
            $this->neutralCount,
            $this->negativeCount,
            $replyRate,
        );
    }
}
