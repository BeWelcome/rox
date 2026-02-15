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
        $preferences = $preferenceRepository->findBy(['status' => 'Normal'], ['position' => 'ASC']);

        return array_filter($preferences, static function ($preference) {
            return Preference::LOCALE !== $preference->getCodename();
        });
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
