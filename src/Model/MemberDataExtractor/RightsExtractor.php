<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\RightVolunteer;

final class RightsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        /** @var RightVolunteer[] $volunteerRights */
        $volunteerRights = $member->getVolunteerRights();

        return $this->writePersonalDataFile(['volunteerrights' => $volunteerRights], 'rights', $tempDir . 'rights.html');
    }
}
