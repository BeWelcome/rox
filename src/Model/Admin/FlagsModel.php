<?php

namespace App\Model\Admin;

use App\Entity\Flag;
use App\Entity\FlagMember;
use App\Entity\Member;
use App\Repository\FlagMemberRepository;
use App\Repository\FlagRepository;
use App\Repository\RightVolunteerRepository;
use DateTime;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

class FlagsModel
{
    public const string ASSIGNMENT_CREATED = 'created';
    public const string ASSIGNMENT_DUPLICATE = 'duplicate';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FlagRepository $flagRepository,
        private readonly FlagMemberRepository $flagMemberRepository,
        private readonly RightVolunteerRepository $rightVolunteerRepository,
    ) {
    }

    /**
     * @return Flag[]
     */
    public function getRelevantFlags(): array
    {
        return $this->flagRepository->findRelevant();
    }

    public function canCreate(Member $manager): bool
    {
        $assignment = $this->rightVolunteerRepository->findActiveForMemberAndName($manager, 'Flags');
        if (null === $assignment) {
            return false;
        }

        $scope = str_replace('"', '', (string) $assignment->getScope());
        $tokens = preg_split('/[;,]/', $scope) ?: [];
        $tokens = array_map(static fn (string $token): string => strtolower(trim($token)), $tokens);

        return \in_array('all', $tokens, true) || \in_array('create', $tokens, true);
    }

    public function assign(
        Member $member,
        Flag $flag,
        int $level,
        string $scope,
        string $comment,
    ): string {
        return $this->entityManager->wrapInTransaction(
            function (EntityManagerInterface $entityManager) use ($member, $flag, $level, $scope, $comment): string {
                $entityManager->lock($member, LockMode::PESSIMISTIC_WRITE);
                $current = $this->flagMemberRepository->findCurrent($member, $flag);
                if (null !== $current) {
                    $entityManager->refresh($current);
                }
                if (null !== $current && 0 !== $current->getLevel()) {
                    return self::ASSIGNMENT_DUPLICATE;
                }

                $assignment = new FlagMember();
                $assignment->setMember($member)
                    ->setFlag($flag)
                    ->setLevel($level)
                    ->setScope($scope)
                    ->setComment($comment);

                if (null !== $current) {
                    $created = new DateTime();
                    if ($created->getTimestamp() <= $current->getCreated()->getTimestamp()) {
                        $created = DateTime::createFromInterface($current->getCreated());
                        $created->modify('+1 second');
                    }
                    $assignment->setCreated($created);
                }

                $entityManager->persist($assignment);

                return self::ASSIGNMENT_CREATED;
            }
        );
    }

    public function edit(FlagMember $assignment, int $level, string $scope, string $comment): bool
    {
        return $this->entityManager->wrapInTransaction(
            function () use ($assignment, $level, $scope, $comment): bool {
                $member = $assignment->getMember();
                $flag = $assignment->getFlag();
                $this->entityManager->lock($member, LockMode::PESSIMISTIC_WRITE);
                $current = $this->flagMemberRepository->findCurrent($member, $flag);
                if (null !== $current) {
                    $this->entityManager->refresh($current);
                }
                if (null === $current || $current->getId() !== $assignment->getId() || 0 === $current->getLevel()) {
                    return false;
                }

                $current->setLevel($level)
                    ->setScope($scope)
                    ->setComment($comment);

                return true;
            }
        );
    }

    public function remove(FlagMember $assignment, Member $manager): bool
    {
        return $this->entityManager->wrapInTransaction(
            function () use ($assignment, $manager): bool {
                $member = $assignment->getMember();
                $flag = $assignment->getFlag();
                $this->entityManager->lock($member, LockMode::PESSIMISTIC_WRITE);
                $current = $this->flagMemberRepository->findCurrent($member, $flag);
                if (null !== $current) {
                    $this->entityManager->refresh($current);
                }
                if (null === $current || $current->getId() !== $assignment->getId() || 0 === $current->getLevel()) {
                    return false;
                }

                $comment = rtrim($current->getComment())
                    . "\n\nRemoved by {$manager->getUsername()} on "
                    . new DateTime()->format('Y-m-d');
                $current->setLevel(0)
                    ->setComment($comment);

                return true;
            }
        );
    }

    public function create(string $name, string $description): ?Flag
    {
        if (null !== $this->flagRepository->findOneByNameCaseInsensitive($name)) {
            return null;
        }

        $flag = new Flag();
        $flag->setName($name)
            ->setDescription($description)
            ->setRelevance(100);
        $this->entityManager->persist($flag);
        $this->entityManager->flush();

        return $flag;
    }

    public function findCurrentAssignment(Member $member, Flag $flag): ?FlagMember
    {
        return $this->flagMemberRepository->findCurrent($member, $flag);
    }

    public function paginateAssignments(
        ?string $username,
        ?int $flagId,
        bool $includeHistory,
        bool $memberFirst,
        int $page,
    ): Pagerfanta {
        return $this->flagMemberRepository->paginateAssignments(
            $username,
            $flagId,
            $includeHistory,
            $memberFirst,
            $page,
        );
    }
}
