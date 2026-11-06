<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;

final class RightsExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): array
    {
        $volunteerRights = $member->getVolunteerRights();

        $this->writePersonalDataFile(['volunteerrights' => $volunteerRights], 'rights', $tempDir . 'rights.html');

        return ['rights' => \count($volunteerRights)];
    }
}
