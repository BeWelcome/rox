<?php

namespace App\Model;

use App\Entity\Member;
use App\Repository\MemberRepository;

class SafetyModel
{
    public function __construct(
        private readonly MemberRepository $memberRepository,
    ) {
    }

    /**
     * @return Member[]
     */
    public function getSafetyTeamMembers(): array
    {
        return $this->memberRepository->findSafetyTeamMembers();
    }
}
