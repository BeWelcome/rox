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
     * @return string the personal data file
     */
    public function extract(Member $member, string $tempDir): string;
}
