<?php

namespace App\Model;

use App\Doctrine\ForumDeleteStatusType;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Repository\GroupRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * The numbers in a group's head: its discussions and members (the counts of the tabs) and when the last post was.
 */
class GroupHeadModel
{
    /** @var array<int, array{threads: int, members: int, lastPost: ?DateTimeInterface}> */
    private array $numbers = [];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array{threads: int, members: int, lastPost: ?DateTimeInterface}
     */
    #[AsTwigFunction('group_numbers')]
    public function getNumbers(Group $group): array
    {
        $id = (int) $group->getId();
        if (!isset($this->numbers[$id])) {
            $threads = $this->entityManager->createQueryBuilder()
                ->select('COUNT(t.id) AS threads', 'MAX(p.created) AS lastPost')
                ->from(ForumThread::class, 't')
                ->leftJoin('t.lastPost', 'p')
                ->where('t.group = :group')
                ->andWhere('t.deleted = :notDeleted')
                ->setParameter('group', $group)
                ->setParameter('notDeleted', ForumDeleteStatusType::NOT_DELETED)
                ->getQuery()
                ->getSingleResult();
            /** @var GroupRepository $groups */
            $groups = $this->entityManager->getRepository(Group::class);

            $this->numbers[$id] = [
                'threads' => (int) $threads['threads'],
                'members' => $groups->countMembers($group),
                'lastPost' => null === $threads['lastPost'] ? null : new DateTimeImmutable($threads['lastPost']),
            ];
        }

        return $this->numbers[$id];
    }
}
