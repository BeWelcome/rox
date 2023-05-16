<?php

namespace App\Utilities;

use App\Entity\Member;
use App\Entity\Preference;

trait ItemsPerPageTraits
{
    private function getItemsPerPage(Member $member): int
    {
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        $itemsPerPagePreference = $preferenceRepository->findOneBy(['codename' => Preference::ITEMS_PER_PAGE]);

        return (int) $member->getMemberPreference($itemsPerPagePreference)->getValue();
    }
}
