<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;

class PreferenceModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getPreferences(): array
    {
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);

        return $preferenceRepository->findBy(['status' => 'Normal'], ['position' => 'ASC']);
    }

    public function getMemberPreferences(Member $member, array $preferences): array
    {
        $memberPreferences = [];
        foreach ($preferences as $preference) {
            $memberPreferences[] = $member->getMemberPreference($preference);
        }

        return $memberPreferences;
    }
}
