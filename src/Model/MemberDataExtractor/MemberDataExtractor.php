<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;

final class MemberDataExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        return $this->writePersonalDataFile(
            [
                'member' => $member,
                'profilepicture' => 'images/empty_avatar.png',
            ],
            'profile',
            $tempDir . 'profile.html'
        );
    }
}
