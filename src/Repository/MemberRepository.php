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
        /** @var Member $user */
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

    public function loadDataRetentionMembers(): array
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.status = :askToLeave')
            ->andWhere('m.username NOT LIKE :retired')
            ->andWhere('DATEDIFF(:now, m.lastLogin) > 365')
            ->setParameter(':askToLeave', MemberStatusType::ASKED_TO_LEAVE)
            ->setParameter(':retired', 'Retired\_%')
            ->setParameter(':now', new DateTime())
            ->getQuery()
        ;

        return $query->getResult();
    }
}
