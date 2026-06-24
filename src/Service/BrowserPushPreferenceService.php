<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BrowserPushPreferenceService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function isEnabled(Member $member): bool
    {
        return 'No' !== $this->getValue($member);
    }

    public function getValue(Member $member): string
    {
        $connection = $this->entityManager->getConnection();
        $preference = $connection->fetchAssociative(
            'SELECT id, DefaultValue FROM preferences WHERE codeName = ?',
            [Preference::BROWSER_NOTIFICATIONS]
        );
        if (false === $preference) {
            return 'Yes';
        }

        $value = $connection->fetchOne(
            'SELECT Value FROM memberspreferences WHERE IdMember = ? AND IdPreference = ?',
            [$member->getId(), (int) $preference['id']]
        );

        return false === $value ? (string) $preference['DefaultValue'] : (string) $value;
    }
}
