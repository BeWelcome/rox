<?php

namespace App\Utilities;

use App\Entity\Member;
use App\Entity\MemberPhoto;
use App\Entity\MembersPhoto;
use App\Entity\MemberTranslation;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;

class AllowContactCheck
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getAllowRequestsWithoutProfilePicture(Member $member): bool
    {
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::ALLOW_CONTACT_WITHOUT_PICTURE]);

        $value = $member->getMemberPreference($preference)->getValue();

        return 'Yes' === $value;
    }

    public function getAllowRequestsWithoutAboutMe(Member $member): bool
    {
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::ALLOW_CONTACT_WITHOUT_ABOUT_ME]);

        $value = $member->getMemberPreference($preference)->getValue();

        return 'Yes' === $value;
    }

    public function checkIfMemberHasProfilePicture(Member $member): bool
    {
        $profilePictureRepository = $this->entityManager->getRepository(MemberPhoto::class);
        $profilePictures = $profilePictureRepository->findBy(['member' => $member]);

        return \count($profilePictures) > 0;
    }

    public function checkIfMemberHasAboutMe(Member $member): bool
    {
        $translations = $member->getTranslations();

        $hasAboutMe = false;
        foreach (array_keys($translations) as $language) {
            $hasAboutMe |= ($translations[$language]['AboutMe'] ?? '') !== '';
        }

        return $hasAboutMe;
    }
}
