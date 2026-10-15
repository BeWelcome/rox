<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MemberRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        /* @var Member $user */
        // set the new hashed password on the User object
        $user->setPassword($newHashedPassword);

        // execute the queries on the database
        $this->getEntityManager()->flush();
    }

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     */
    public function loadMembersByUsernamePart($username)
    {
        return $this->createQueryBuilder('u')
            ->select('u.username')
            ->where('u.username Like :username')
            ->setParameter('username', '%' . $username . '%')
            ->andWhere('u.status in (:status)')
            ->setParameter(':status', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->setMaxResults(10)
            ->orderBy('u.username', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function loadUserByIdentifier(string $usernameOrEmail): ?Member
    {
        if (empty($usernameOrEmail)) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $usernameOrEmail)
            ->setParameter('email', $usernameOrEmail)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function loadUserByUsername(string $username)
    {
        return $this->loadUserByIdentifier($username);
    }

    public function findByProfileInfo($term)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username like :term OR u.email like :term')
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function findByProfileInfoStartsWith($term)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username like :term')
            ->setParameter('term', $term . '%')
            ->andWhere('u.status in (:status)')
            ->setParameter(':status', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function loadDataRetentionMembers(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.status = :askToLeave')
            ->andWhere('m.username NOT LIKE :retired')
            ->andWhere('(DATEDIFF(:now, m.lastLogin) > 365 OR (m.lastLogin IS NULL AND DATEDIFF(:now, m.created) > 365))')
            ->setParameter(':askToLeave', MemberStatusType::ASKED_TO_LEAVE)
            ->setParameter(':retired', 'Retired\_%')
            ->setParameter(':now', new DateTime())
            ->orderBy('m.id', 'ASC')
        ;

        if (null !== $limit && $limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findMembersToRemind(int $batchSize): array
    {
        $qb = $this->createQueryBuilder('m');
        $expr = $qb->expr();
        $qb
            ->where(
                $expr->in(
                    'm.status',
                    [MemberStatusType::ACTIVE, MemberStatusType::CHOICE_INACTIVE, MemberStatusType::OUT_OF_REMIND]
                )
            )
            ->andWhere(
                $expr->orX(
                    $expr->gt('DATEDIFF(CURRENT_DATE(), m.lastLogin)', 365),
                    $expr->andX(
                        $expr->isNull('m.lastLogin'),
                        $expr->gt('DATEDIFF(CURRENT_DATE(), m.created)', 365)
                    )
                )
            )
            ->andWhere('YEARDIFF(m.updated, CURRENT_DATE()) > 0')
            ->orderBy('m.id', 'ASC')
            ->setMaxResults($batchSize);

        return $qb->getQuery()->getResult();
    }

    /**
     * returns a list of members that have not confirmed their email address while creating the account more
     * than 2 weeks ago.
     */
    public function findMailNotConfirmedMembers(): array
    {
        $qb = $this->createQueryBuilder('m');
        $expr = $qb->expr();
        $qb
            ->where(
                $expr->in(
                    'm.status',
                    [MemberStatusType::AWAITING_MAIL_CONFIRMATION]
                )
            )
            ->andWhere('DATEDIFF(CURRENT_DATE(), m.created) > 14')
            ->orderBy('m.id', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findMembersToSuspend(mixed $batchSize): array
    {
        $qb = $this->createQueryBuilder('m');
        $expr = $qb->expr();
        $qb
            ->where(
                $expr->in(
                    'm.status',
                    [MemberStatusType::OUT_OF_REMIND]
                )
            )
            ->andWhere('YEARDIFF(m.lastLogin, CURRENT_DATE()) > 4')
            ->orderBy('m.id', 'ASC')
            ->setMaxResults($batchSize);
        ;

        return $qb->getQuery()->getResult();
    }
}
