<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MemberRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
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

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return UserInterface|null
     */
    public function loadUserByUsername($username)
    {
        if (empty($username)) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
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

    public function loadDataRetentionMembers()
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.status = :askToLeave')
            ->andWhere('m.username NOT LIKE :retired')
            ->andWhere('DATEDIFF(:now, m.lastLogin) > 365')
            ->setParameter(':askToLeave', MemberStatusType::ASKED_TO_LEAVE)
            ->setParameter(':retired', 'Retired\_%')
            ->setParameter(':now', new DateTime())
            ->setMaxResults(20)
            ->getQuery()
        ;

        return $query->getResult();
    }
}
