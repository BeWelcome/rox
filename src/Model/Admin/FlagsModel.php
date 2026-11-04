<?php

namespace App\Model\Admin;

use App\Entity\Flag;
use App\Entity\FlagMember;
use App\Entity\Member;
use App\Repository\FlagMemberRepository;
use App\Repository\FlagRepository;
use DateTime;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The one flag in use, NotAllowedToPostInForum: set and removed in the Admin box of the member's profile, every
 * assignment ever made in the flags list.
 *
 * Each block is a new flagsmembers row: main's code reads a member's newest row of a flag, so a removed block (level
 * 0) stays as history and blocking again starts a new row.
 */
class FlagsModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FlagRepository $flagRepository,
        private readonly FlagMemberRepository $flagMemberRepository,
    ) {
    }

    public function getForumPostingFlag(): ?Flag
    {
        return $this->flagRepository->findForumPostingFlag();
    }

    /**
     * The member's current block (the newest row, not removed), or null.
     */
    public function findBlock(Member $member, Flag $flag): ?FlagMember
    {
        $current = $this->flagMemberRepository->findCurrent($member, $flag);

        return null !== $current && 0 !== $current->getLevel() ? $current : null;
    }

    public function countRemoved(Member $member, Flag $flag): int
    {
        return $this->flagMemberRepository->countRemoved($member, $flag);
    }

    /**
     * @return list<array<string, mixed>> see FlagMemberRepository::findAllOfFlag()
     */
    public function findAllOfFlag(Flag $flag, ?string $username): array
    {
        return $this->flagMemberRepository->findAllOfFlag($flag, $username);
    }

    /**
     * Blocks the member: a new assignment (level 1, no scope) with the comment. False if they're blocked already.
     */
    public function block(Member $member, Flag $flag, string $comment): bool
    {
        return $this->entityManager->wrapInTransaction(
            function (EntityManagerInterface $entityManager) use ($member, $flag, $comment): bool {
                $entityManager->lock($member, LockMode::PESSIMISTIC_WRITE);
                $current = $this->flagMemberRepository->findCurrent($member, $flag);
                if (null !== $current) {
                    $entityManager->refresh($current);
                    if (0 !== $current->getLevel()) {
                        return false;
                    }
                }

                $assignment = new FlagMember();
                $assignment->setMember($member)
                    ->setFlag($flag)
                    ->setLevel(1)
                    ->setScope('')
                    ->setComment($comment);
                if (null !== $current) {
                    // newer than the removed one, which main reads as the current row otherwise
                    $created = new DateTime();
                    if ($created->getTimestamp() <= $current->getCreated()->getTimestamp()) {
                        $created = DateTime::createFromInterface($current->getCreated());
                        $created->modify('+1 second');
                    }
                    $assignment->setCreated($created);
                }
                $entityManager->persist($assignment);

                return true;
            }
        );
    }

    /**
     * Removes the block: its row stays with level 0 and who removed it. False if the member isn't blocked (any more),
     * or $assignment isn't the current block.
     */
    public function unblock(FlagMember $assignment, Member $manager): bool
    {
        return $this->entityManager->wrapInTransaction(
            function () use ($assignment, $manager): bool {
                $member = $assignment->getMember();
                $this->entityManager->lock($member, LockMode::PESSIMISTIC_WRITE);
                $current = $this->flagMemberRepository->findCurrent($member, $assignment->getFlag());
                if (null !== $current) {
                    $this->entityManager->refresh($current);
                }
                if (null === $current || $current->getId() !== $assignment->getId() || 0 === $current->getLevel()) {
                    return false;
                }

                $current->setLevel(0)
                    ->setComment(rtrim($current->getComment())
                        . "\n\nRemoved by {$manager->getUsername()} on "
                        . new DateTime()->format('Y-m-d'));

                return true;
            }
        );
    }
}
