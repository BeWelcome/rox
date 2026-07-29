<?php

namespace App\Model\Admin;

use App\Entity\Member;
use App\Entity\Right;
use App\Entity\RightVolunteer;
use App\Repository\RightRepository;
use App\Repository\RightVolunteerRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

class RightsModel
{
    public const string ASSIGNMENT_CREATED = 'created';
    public const string ASSIGNMENT_REACTIVATED = 'reactivated';
    public const string ASSIGNMENT_DUPLICATE = 'duplicate';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RightRepository $rightRepository,
        private readonly RightVolunteerRepository $rightVolunteerRepository,
    ) {
    }

    /**
     * @return Right[]
     */
    public function getManagedRights(Member $manager): array
    {
        $scope = $this->getManagementScope($manager);
        $rights = $this->rightRepository->findAllOrdered();
        if (\in_array('all', $scope, true)) {
            return $rights;
        }

        return array_values(array_filter(
            $rights,
            static fn (Right $right): bool => \in_array(strtolower($right->getName()), $scope, true),
        ));
    }

    public function canManage(Member $manager, Right $right): bool
    {
        $scope = $this->getManagementScope($manager);

        return \in_array('all', $scope, true)
            || \in_array(strtolower($right->getName()), $scope, true);
    }

    public function canCreate(Member $manager): bool
    {
        $scope = $this->getManagementScope($manager);

        return \in_array('all', $scope, true) || \in_array('create', $scope, true);
    }

    public function isScopeWellFormed(string $scope): bool
    {
        $tokens = $this->parseScope($scope);

        return null !== $tokens && [] !== $tokens;
    }

    public function assign(
        Member $member,
        Right $right,
        int $level,
        string $scope,
        string $comment,
    ): string {
        $assignment = $this->rightVolunteerRepository->findAssignment($member, $right);
        if (null !== $assignment && 0 !== $assignment->getLevel()) {
            return self::ASSIGNMENT_DUPLICATE;
        }

        $result = self::ASSIGNMENT_REACTIVATED;
        if (null === $assignment) {
            $assignment = new RightVolunteer();
            $assignment->setMember($member);
            $assignment->setRight($right);
            $result = self::ASSIGNMENT_CREATED;
        }

        $assignment->setLevel($level)
            ->setScope($scope)
            ->setComment($comment);
        $this->entityManager->persist($assignment);
        $this->entityManager->flush();

        return $result;
    }

    public function edit(
        RightVolunteer $assignment,
        int $level,
        string $scope,
        string $comment,
    ): void {
        $assignment->setLevel($level)
            ->setScope($scope)
            ->setComment($comment);
        $this->entityManager->flush();
    }

    public function remove(RightVolunteer $assignment, Member $manager): void
    {
        $comment = rtrim((string) $assignment->getComment())
            . "\n\nRemoved by {$manager->getUsername()} on "
            . new DateTime()->format('Y-m-d');
        $assignment->setLevel(0)
            ->setComment($comment);
        $this->entityManager->flush();
    }

    public function create(string $name, string $description): ?Right
    {
        if (null !== $this->rightRepository->findOneByNameCaseInsensitive($name)) {
            return null;
        }

        $right = new Right();
        $right->setName($name)
            ->setDescription($description);
        $this->entityManager->persist($right);
        $this->entityManager->flush();

        return $right;
    }

    public function findAssignment(Member $member, Right $right): ?RightVolunteer
    {
        return $this->rightVolunteerRepository->findAssignment($member, $right);
    }

    public function paginateAssignments(
        Member $manager,
        ?string $username,
        ?int $rightId,
        bool $includeHistory,
        bool $memberFirst,
        int $page,
    ): Pagerfanta {
        $allowedNames = array_map(
            static fn (Right $right): string => $right->getName(),
            $this->getManagedRights($manager),
        );

        return $this->rightVolunteerRepository->paginateAssignments(
            $allowedNames,
            $username,
            $rightId,
            $includeHistory,
            $memberFirst,
            $page,
        );
    }

    /**
     * @return string[]
     */
    private function getManagementScope(Member $manager): array
    {
        $assignment = $this->rightVolunteerRepository->findActiveForMemberAndName($manager, 'Rights');
        if (null === $assignment) {
            return [];
        }

        $tokens = $this->parseScope((string) $assignment->getScope());

        return $tokens ?? [];
    }

    /**
     * @return string[]|null
     */
    private function parseScope(string $scope): ?array
    {
        $scope = trim($scope);
        if ('' === $scope) {
            return [];
        }

        $tokens = [];
        $offset = 0;
        $length = \strlen($scope);

        while ($offset < $length) {
            while ($offset < $length && ctype_space($scope[$offset])) {
                ++$offset;
            }
            if ($offset >= $length) {
                break;
            }

            if ('"' === $scope[$offset]) {
                $closingQuote = strpos($scope, '"', $offset + 1);
                if (false === $closingQuote) {
                    return null;
                }
                $token = substr($scope, $offset + 1, $closingQuote - $offset - 1);
                $offset = $closingQuote + 1;
                while ($offset < $length && ctype_space($scope[$offset])) {
                    ++$offset;
                }
                if ($offset < $length && !\in_array($scope[$offset], [',', ';'], true)) {
                    return null;
                }
            } else {
                $start = $offset;
                while ($offset < $length && !\in_array($scope[$offset], [',', ';'], true)) {
                    if ('"' === $scope[$offset]) {
                        return null;
                    }
                    ++$offset;
                }
                $token = trim(substr($scope, $start, $offset - $start));
            }

            if ('' === $token) {
                return null;
            }
            $tokens[] = strtolower($token);

            if ($offset >= $length) {
                break;
            }
            ++$offset;
        }

        return array_values(array_unique($tokens));
    }
}
