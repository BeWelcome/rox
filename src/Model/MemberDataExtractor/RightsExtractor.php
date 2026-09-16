<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;

final class RightsExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): string
    {
        $volunteerRights = $member->getVolunteerRights();

        return $this->writePersonalDataFile(['volunteerrights' => $volunteerRights], 'rights', $tempDir . 'rights.html');
    }
}
