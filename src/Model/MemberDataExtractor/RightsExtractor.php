<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\NewMember as Member;
use App\Entity\RightVolunteer;

final class RightsExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): string
    {
        /** @var RightVolunteer[] $volunteerRights */
        $volunteerRights = $member->getVolunteerRights();

        return $this->writePersonalDataFile(['volunteerrights' => $volunteerRights], 'rights', $tempDir . 'rights.html');
    }
}
