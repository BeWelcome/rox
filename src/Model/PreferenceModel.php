<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Preference;
use App\Form\DataTransformer\MemberPreferenceTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PreferenceModel
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getPreferences(): array
    {
        /** @var EntityRepository $preferenceRepository*/
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        $preferences = $preferenceRepository->findBy(['status' => 'Normal'], ['position' => 'ASC']);

        return array_filter($preferences, function ($p) {
            return (Preference::LOCALE != $p->getCodename());
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
