<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;

interface ExtractorInterface
{
    /**
     * Extract Member personal data.
     *
     * @param Member $member  the member
     * @param string $tempDir the temporary directory to store personal data files
     *
     * @return array<string, int> the sections written (MyDataSections) and how many items each has
     */
    public function extract(Member $member, string $tempDir): array;
}
