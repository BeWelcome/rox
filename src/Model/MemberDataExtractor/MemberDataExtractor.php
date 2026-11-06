<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\MemberPhoto;

/**
 * The profile page, with the member's profile picture as ProfilePicturesExtractor copies it into pictures/.
 */
final class MemberDataExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): array
    {
        $photos = $this->getRepository(MemberPhoto::class)->findBy(['member' => $member], ['created' => 'DESC']);
        $this->writePersonalDataFile(['member' => $member, 'picture' => ProfilePicture::files($photos)], 'profile', $tempDir . 'profile.html');

        return ['profile' => 1];
    }
}
