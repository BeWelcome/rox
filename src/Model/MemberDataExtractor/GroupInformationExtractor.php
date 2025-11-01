<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\NewMember as Member;
use Exception;

final class GroupInformationExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): string
    {
        // Groups the member is in and why
        $memberships = [];
        $groupMemberships = $member->getGroupMemberships();
        if (!empty($groupMemberships)) {
            foreach ($groupMemberships as $groupMembership) {
                try {
                    // Database is messy. Check if group still exists
                    if ($groupMembership->getGroup()->getName()) {
                        $memberships[] = $groupMembership;
                    }
                } catch (Exception) {
                    // Deleted Group
                }
            }
        }

        return $this->writePersonalDataFile(['groupmemberships' => $memberships], 'groups', $tempDir . 'groups.html');
    }
}
