<?php

namespace App\Dto;

/**
 * How many hosting requests and invitations a member received and how many of them they answered. Only the
 * first message of a conversation counts; a request is answered when the member wrote any message in it.
 */
readonly class ReplyRate
{
    public function __construct(
        public int $received,
        public int $answered,
    ) {
    }

    /**
     * Share of answered requests between 0 and 1, null when the member never received a request.
     */
    public function getRate(): ?float
    {
        return 0 === $this->received ? null : $this->answered / $this->received;
    }

    /**
     * Share of answered requests in whole percent, null when the member never received a request.
     */
    public function getPercentage(): ?int
    {
        $rate = $this->getRate();

        return null === $rate ? null : (int) round($rate * 100);
    }
}
