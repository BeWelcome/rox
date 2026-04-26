<?php

namespace App\Repository;

use App\Doctrine\AccommodationType;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Form\CustomDataClass\SearchFormRequest;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{
    public const int ORDER_USERNAME = 2;
    public const int ORDER_ACCOMMODATION = 6;
    public const int ORDER_LOGIN = 8;
    public const int ORDER_MEMBERSHIP = 10;
    public const int ORDER_COMMENTS = 12;
    public const int ORDER_DISTANCE = 14;

    public const int DIRECTION_ASCENDING = 1;
    public const int DIRECTION_DESCENDING = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
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
    public function loadMembersByUsernamePart(string $username)
    {
        return $this->createQueryBuilder('u')
            ->select('u.username')
            ->where('u.username Like :username')
            ->setParameter('username', '%' . $username . '%')
            ->andWhere('u.status in (:status)')
            ->setParameter('status', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->setMaxResults(10)
            ->orderBy('u.username', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function loadUserByIdentifier(string $identifier): ?Member
    {
        if (empty($identifier)) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $identifier)
            ->setParameter('email', $identifier)
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
            ->where('u.username like :term')
            ->orWhere('u.email like :term')
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
            ->setParameter('status', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function loadDataRetentionMembers(): array
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.status = :askToLeave')
            ->andWhere('m.username NOT LIKE :retired')
            ->andWhere('DATEDIFF(:now, m.lastActive) > 365')
            ->setParameter('askToLeave', MemberStatusType::ASKED_TO_LEAVE)
            ->setParameter('retired', 'Retired\_%')
            ->setParameter('now', new DateTime())
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function applySearchFilters(QueryBuilder $qb, SearchFormRequest $searchRequest): void
    {
        // Status
        $qb->andWhere('m.status IN (:activeStatus)')
            ->setParameter('activeStatus', MemberStatusType::ACTIVE_SEARCH_ARRAY);

        // Last Active
        if ($searchRequest->last_active) {
            $qb->andWhere('m.lastActive >= :lastActiveDate')
                ->setParameter('lastActiveDate', new DateTimeImmutable("-{$searchRequest->last_active} months"));
        }

        // Gender
        if ($searchRequest->gender) {
            $qb->andWhere($qb->expr()->in('m.gender', ':gender'))
               ->andWhere('BIT_AND(m.hideAttribute, :genderHiddenFlag) = 0')
               ->setParameter('gender', $searchRequest->gender)
               ->setParameter('genderHiddenFlag', Member::GENDER_HIDDEN);
        }

        // Comments
        if ($searchRequest->has_comments) {
            $hasCommentsQb = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from('App\Entity\Comment', 'commentFilterTable')
                ->where('commentFilterTable.toMember = m.id');

            $qb->andWhere($qb->expr()->exists($hasCommentsQb->getDQL()));
        }

        // Age
        if ($searchRequest->min_age || $searchRequest->max_age) {
            $now = new DateTimeImmutable();

            if ($searchRequest->min_age) {
                $maxBirthDate = $now->sub(new DateInterval("P{$searchRequest->min_age}Y"));
                $qb->andWhere('m.birthdate <= :maxBirthDate')
                    ->setParameter('maxBirthDate', $maxBirthDate);
            }

            if ($searchRequest->max_age) {
                $minBirthDate = $now->sub(new DateInterval("P{$searchRequest->max_age}Y"));
                $qb->andWhere('m.birthdate >= :minBirthDate')
                    ->setParameter('minBirthDate', $minBirthDate);
            }

            if ($searchRequest->min_age || $searchRequest->max_age) {
                $qb->andWhere('BIT_AND(m.hideAttribute, :ageHiddenFlag) = 0')
                   ->setParameter('ageHiddenFlag', Member::AGE_HIDDEN);
            }
        }

        // Can Host
        if ($searchRequest->can_host > 0) {
            $qb->andWhere('m.maxGuests >= :canHost')
                ->setParameter('canHost', $searchRequest->can_host);
        }

        // Accommodation
        $accommodationTypes = [];
        if ($searchRequest->accommodation_yes) {
            $accommodationTypes[] = AccommodationType::YES;
        }
        if ($searchRequest->accommodation_no) {
            $accommodationTypes[] = AccommodationType::NO;
        }

        if (!empty($accommodationTypes)) {
            $qb->andWhere($qb->expr()->in('m.accommodation', ':accommodations'))
               ->setParameter('accommodations', $accommodationTypes);
        }

        // Groups
        if (!empty($searchRequest->groups)) {
            $groupQb = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from('App\Entity\GroupMembership', 'gm_sub')
                ->where('gm_sub.member = m.id')
                ->andWhere('gm_sub.group IN (:groups)');

            $qb->andWhere($qb->expr()->exists($groupQb->getDQL()))
               ->setParameter('groups', $searchRequest->groups);
        }

        // Languages
        if (!empty($searchRequest->languages)) {
            $langQb = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from('App\Entity\MemberLanguageLevel', 'mll_sub')
                ->where('mll_sub.member = m.id')
                ->andWhere('mll_sub.language IN (:languages)');

            $qb->andWhere($qb->expr()->exists($langQb->getDQL()))
               ->setParameter('languages', $searchRequest->languages);
        }

        // Has Profile Picture
        if ($searchRequest->has_profile_picture) {
            $picQb = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from('App\Entity\MemberPhoto', 'mp_sub')
                ->where('mp_sub.member = m.id');
            $qb->andWhere($qb->expr()->exists($picQb->getDQL()));
        }

        // Keywords (Search text)
        if ($searchRequest->keywords) {
            $keyword = '%' . $searchRequest->keywords . '%';

            $qb
                ->leftJoin('m.translations', 'mt_keyword', Join::WITH, 'mt_keyword.content LIKE :keyword')
                ->andWhere(
                    $qb->expr()->orX(
                        'mt_keyword.id IS NOT NULL',
                        'm.username LIKE :keyword',
                        'm.name LIKE :keyword',
                        'm.aboutMe LIKE :keyword',
                        'm.hobbies LIKE :keyword',
                        'm.books LIKE :keyword',
                        'm.music LIKE :keyword',
                        'm.movies LIKE :keyword',
                        'm.occupation LIKE :keyword',
                        'm.pastTrips LIKE :keyword',
                        'm.plannedTrips LIKE :keyword',
                        'm.pleaseBring LIKE :keyword',
                        'm.whereYouSleep LIKE :keyword',
                        'm.offerGuests LIKE :keyword',
                        'm.offerHosts LIKE :keyword',
                        'm.gettingThere LIKE :keyword',
                        'm.organizations LIKE :keyword',
                        'm.additionalAccommodationInfo LIKE :keyword',
                        'm.iLiveWith LIKE :keyword',
                        'm.maxLengthOfStay LIKE :keyword',
                        'm.houseRules LIKE :keyword'
                    )
                )
               ->setParameter('keyword', $keyword)
            ;
        }

        // Has About Me
        if ($searchRequest->has_about_me) {
            $qb->leftJoin('m.translations', 'mt_about', Join::WITH, "mt_about.field = 'aboutMe' AND mt_about.content != ''")
                ->andWhere(
                    $qb->expr()->orX(
                        "(m.aboutMe IS NOT NULL AND m.aboutMe != '')",
                        'mt_about.id IS NOT NULL'
                    )
                )
            ;
        }

        // Restrictions
        if ($searchRequest->no_smoking) {
            $qb->andWhere('m.restrictions LIKE :no_smoking')
               ->setParameter('no_smoking', '%' . \App\Doctrine\HostRestrictionsType::NO_SMOKING . '%');
        }
        if ($searchRequest->no_alcohol) {
            $qb->andWhere('m.restrictions LIKE :no_alcohol')
               ->setParameter('no_alcohol', '%' . \App\Doctrine\HostRestrictionsType::NO_ALCOHOL . '%');
        }
        if ($searchRequest->no_drugs) {
            $qb->andWhere('m.restrictions LIKE :no_drugs')
               ->setParameter('no_drugs', '%' . \App\Doctrine\HostRestrictionsType::NO_DRUGS . '%');
        }

        // Offers
        if ($searchRequest->offers_dinner) {
            $qb->andWhere('m.standardOffers LIKE :offers_dinner')
               ->setParameter('offers_dinner', '%' . \App\Doctrine\StandardOffersType::DINNER . '%');
        }
        if ($searchRequest->offers_tour) {
            $qb->andWhere('m.standardOffers LIKE :offers_tour')
               ->setParameter('offers_tour', '%' . \App\Doctrine\StandardOffersType::GUIDED_TOUR . '%');
        }
    }
}
