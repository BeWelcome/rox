<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BrowserPushPreferenceService
{
    public const string VALUE_NO = 'No';
    public const string VALUE_OPEN_ONLY = 'OpenOnly';
    public const string VALUE_ALWAYS = 'Always';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function isEnabled(Member $member): bool
    {
        return self::VALUE_NO !== $this->getValue($member);
    }

    public function isAlways(Member $member): bool
    {
        return self::VALUE_ALWAYS === $this->getValue($member);
    }

    public function isOpenOnly(Member $member): bool
    {
        return self::VALUE_OPEN_ONLY === $this->getValue($member);
    }

    public function getValue(Member $member): string
    {
        $connection = $this->entityManager->getConnection();
        $preference = $connection->fetchAssociative(
            'SELECT id, DefaultValue FROM preferences WHERE codeName = ?',
            [Preference::BROWSER_NOTIFICATIONS]
        );
        if (false === $preference) {
            return self::VALUE_NO;
        }

        $value = $connection->fetchOne(
            'SELECT Value FROM memberspreferences WHERE IdMember = ? AND IdPreference = ?',
            [$member->getId(), (int) $preference['id']]
        );

        return self::normalize(false === $value ? (string) $preference['DefaultValue'] : (string) $value);
    }

    public static function normalize(string $value): string
    {
        return match ($value) {
            'Yes' => self::VALUE_ALWAYS,
            self::VALUE_OPEN_ONLY => self::VALUE_OPEN_ONLY,
            self::VALUE_ALWAYS => self::VALUE_ALWAYS,
            default => self::VALUE_NO,
        };
    }
}
