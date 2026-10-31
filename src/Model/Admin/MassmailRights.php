<?php

namespace App\Model\Admin;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\RightVolunteer;

/**
 * What a volunteer may do with mass mails, from the level and the scope of their MassMail right.
 *
 * The scope names the types of mass mails ("specific", "general", "termsofuse"), how recipients are chosen
 * ("members", "location" or "location:DE,FR", "group" or "group:12,34", "termsofuse") and whether the type of a
 * mass mail may be changed ("changetype"). Without a type, or without a way to choose recipients, all of them are
 * allowed; "All" allows everything. Level 5 (or "All") triggers the sending.
 */
final readonly class MassmailRights
{
    public const string MEMBERS = 'members';
    public const string LOCATION = 'location';
    public const string GROUP = 'group';
    public const string TERMS_OF_USE = 'termsofuse';

    private const array TYPES = [
        'specific' => Newsletter::SPECIFIC_NEWSLETTER,
        'general' => Newsletter::REGULAR_NEWSLETTER,
        'termsofuse' => Newsletter::TERMS_OF_USE,
    ];

    /**
     * @param list<string> $types      the types of mass mails the volunteer creates
     * @param list<string> $recipients the ways to choose recipients
     * @param list<string> $countries  the countries of the location (empty: all)
     * @param list<int>    $groups     the groups (empty: all)
     */
    public function __construct(
        public array $types,
        public array $recipients,
        public array $countries,
        public array $groups,
        public bool $canChangeType,
        public bool $canTrigger,
    ) {
    }

    public static function of(Member $member): self
    {
        $level = 0;
        $scope = '';
        /** @var RightVolunteer $volunteerRight */
        foreach ($member->getVolunteerRights() as $volunteerRight) {
            if (0 === strcasecmp((string) $volunteerRight->getRight()->getName(), 'MassMail')) {
                $level = (int) $volunteerRight->getLevel();
                $scope = strtolower((string) $volunteerRight->getScope());
            }
        }

        return self::fromScope($level, $scope);
    }

    public static function fromScope(int $level, string $scope): self
    {
        $scope = strtolower($scope);
        $all = 1 === preg_match('/\ball\b/', $scope);
        $has = static fn (string $word): bool => 1 === preg_match('/\b' . $word . '\b/', $scope);

        $types = $all ? array_values(self::TYPES) : array_values(array_filter(
            self::TYPES,
            static fn (string $token): bool => $has($token),
            \ARRAY_FILTER_USE_KEY,
        ));
        $recipients = $all ? [] : array_values(array_filter(
            [self::MEMBERS, self::LOCATION, self::GROUP, self::TERMS_OF_USE],
            $has,
        ));

        $countries = [];
        if (!$all && 1 === preg_match('/location:([a-z ,]+)/', $scope, $match)) {
            $countries = array_values(array_filter(array_map(
                static fn (string $code): string => strtoupper(trim($code)),
                explode(',', $match[1]),
            )));
        }
        $groups = [];
        if (!$all && 1 === preg_match('/group:([0-9 ,]+)/', $scope, $match)) {
            $groups = array_values(array_filter(array_map(intval(...), explode(',', $match[1]))));
        }

        return new self(
            [] === $types ? array_values(self::TYPES) : $types,
            [] === $recipients ? [self::MEMBERS, self::LOCATION, self::GROUP, self::TERMS_OF_USE] : $recipients,
            $countries,
            $groups,
            $all || $has('changetype'),
            $all || $level >= 5,
        );
    }

    public function canChoose(string $recipients): bool
    {
        return \in_array($recipients, $this->recipients, true);
    }
}
