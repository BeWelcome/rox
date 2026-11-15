<?php

namespace App\Model;

use App\Doctrine\CommentQualityType;
use App\Doctrine\MemberStatusType;
use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\HostingRequest;
use App\Entity\Leg;
use App\Entity\Member;
use App\Entity\MemberLanguageLevel;
use App\Entity\Message;
use App\Entity\Statistic;
use App\Repository\StatisticsRepository;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 *
 * \todo Move statistics of different aspects  into different models
 */
class StatisticsModel
{
    /** The rows of a list of the statistics page (languages, countries): the last one adds up the others */
    private const int LIST_ROWS = 8;

    /**
     * The statuses the daily numbers count as active members.
     */
    private const array DAILY_ACTIVE_STATUSES = [
        MemberStatusType::ACTIVE,
        MemberStatusType::CHOICE_INACTIVE,
        MemberStatusType::OUT_OF_REMIND,
    ];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getStatisticsHomepage(): array
    {
        // Five counts over whole tables; the numbers don't need to be live.
        return $this->cache->get('homepage_statistics', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            $activeAll = MemberStatusType::ACTIVE_ALL_ARRAY;

            $members = $this->entityManager
                ->createQuery('SELECT COUNT(m.id) FROM ' . Member::class . ' m WHERE m.status IN (:statuses)')
                ->setParameter('statuses', $activeAll)
                ->getSingleScalarResult();

            // The countries of the places of the members who can be found (all their addresses). Native SQL: the
            // distinct places first (a subquery in FROM, which DQL lacks), then their countries; that looks up each
            // place once instead of once per address.
            $countries = (int) $this->entityManager->getConnection()->fetchOne(
                'SELECT COUNT(DISTINCT l.country_id) FROM location l
                 INNER JOIN (
                     SELECT DISTINCT a.location FROM address a INNER JOIN member m ON m.id = a.member_id
                     WHERE m.Status IN (:statuses)
                 ) places ON places.location = l.geoname_id',
                ['statuses' => [MemberStatusType::ACTIVE, MemberStatusType::OUT_OF_REMIND]],
                ['statuses' => ArrayParameterType::STRING],
            );

            // the members as a subquery: MariaDB reads the language levels in member order and checks each member's
            // status once; the language itself needs no join (a foreign key)
            $languages = $this->entityManager
                ->createQuery('SELECT COUNT(DISTINCT IDENTITY(mll.language)) FROM ' . MemberLanguageLevel::class
                    . ' mll WHERE IDENTITY(mll.member) IN (SELECT m.id FROM ' . Member::class
                    . ' m WHERE m.status IN (:statuses))')
                ->setParameter('statuses', $activeAll)
                ->getSingleScalarResult();

            $positiveComments = $this->entityManager
                ->createQuery('SELECT COUNT(c.id) FROM ' . Comment::class . ' c JOIN c.fromMember m'
                    . ' WHERE c.quality = :positive AND m.status IN (:statuses)')
                ->setParameter('positive', CommentQualityType::POSITIVE)
                ->setParameter('statuses', $activeAll)
                ->getSingleScalarResult();

            $activities = $this->entityManager
                ->createQuery('SELECT COUNT(a.id) FROM ' . Activity::class . ' a WHERE a.status = :active')
                ->setParameter('active', Activity::STATUS_ACTIVE)
                ->getSingleScalarResult();

            $stats = [
                'members' => $members,
                'countries' => $countries,
                'languages' => $languages,
                'comments' => $positiveComments,
                'activities' => $activities,
            ];

            return $stats;
        });
    }

    public function updateStatistics(DatePeriod $dates, OutputInterface $output): int
    {
        $progressBar = null;
        $count = iterator_count($dates);
        $output->write(['Update statistics ']);
        if (1 !== $count) {
            // the period ends before its end date
            $lastDate = DateTimeImmutable::createFromInterface($dates->getEndDate())->modify('-1 day');
            $output->writeln(['from ' . $dates->getStartDate()->format('Y-m-d')
                . ' to ' . $lastDate->format('Y-m-d'),
                '',
            ]);
            $progressBar = new ProgressBar($output, $count);
            $progressBar->start();
        } else {
            $output->writeln([
                'for ' . $dates->getStartDate()->format('Y-m-d'),
                '',
            ]);
        }

        $em = $this->entityManager;
        $statisticsRepository = $em->getRepository(Statistic::class);
        /** @var DateTime $day */
        foreach ($dates as $day) {
            if ($progressBar) {
                // advances the progress bar 1 unit
                $progressBar->advance();
            }
            $nextDay = clone $day;
            $nextDay->modify('+1 day');
            $current = $day->format('Y-m-d H:i:s');
            $next = $nextDay->format('Y-m-d H:i:s');

            // First check if for this date an entry already exists
            // if not new do not touch last logins as they would be wrong
            $new = false;
            $statistics = $statisticsRepository->findOneBy(['created' => $day]);
            if (null === $statistics) {
                $statistics = new Statistic();
                $statistics->setCreated($day);
                $new = true;
            }
            $this->setMemberInfo($current, $statistics);

            if ($new) {
                $this->setLoggedInMembers($current, $next, $statistics);
            }
            $this->setMessagesSentAndRead($current, $next, $statistics);
            $this->setRequestsSentAndAccepted($current, $next, $statistics);
            $this->setLegsCreated($current, $next, $statistics);
            $this->setInvitationsSentAndAccepted($current, $next, $statistics);

            $em->persist($statistics);
        }
        if ($progressBar) {
            // ensures that the progress bar is at 100%
            $progressBar->finish();
        }

        $em->flush();

        return 0;
    }

    public function getMembersData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);
        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getMembersDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getMembersDataDaily());
    }

    public function getSentMessagesData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);

        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getSentMessagesDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getSentMessagesDataDaily());
    }

    public function getReadMessagesData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);

        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getReadMessagesDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getReadMessagesDataDaily());
    }

    public function getSentRequestsData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);

        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getSentRequestsDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getSentRequestsDataDaily());
    }

    public function getAcceptedRequestsData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);

        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getAcceptedRequestsDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getAcceptedRequestsDataDaily());
    }

    public function getSentInvitationsData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);

        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getSentInvitationsDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getSentInvitationsDataDaily());
    }

    public function getAcceptedInvitationsData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);

        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getAcceptedInvitationsDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getAcceptedInvitationsDataDaily());
    }

    public function getLegsCreatedData($period): array
    {
        /** @var StatisticsRepository $statisticsRepository */
        $statisticsRepository = $this->entityManager->getRepository(Statistic::class);

        if ('weekly' === $period) {
            return $this->prepareWeeklyData($statisticsRepository->getCreatedLegsDataWeekly());
        }

        return $this->prepareDailyData($statisticsRepository->getCreatedLegsDataDaily());
    }

    /**
     * The languages members speak, by number of members; cached for an hour like the home page numbers.
     */
    public function getLanguagesData(): array
    {
        $resultSet = $this->cache->get('statistics_languages', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            // the members as a subquery: MariaDB reads the language levels in member order (see the home page)
            $rows = $this->entityManager
                ->createQuery('SELECT IDENTITY(mll.language) AS language, COUNT(IDENTITY(mll.member)) AS cnt FROM '
                    . MemberLanguageLevel::class . ' mll WHERE IDENTITY(mll.member) IN (SELECT m.id FROM '
                    . Member::class . ' m WHERE m.status IN (:statuses)) GROUP BY language ORDER BY cnt DESC')
                ->setParameter('statuses', MemberStatusType::ACTIVE_ALL_ARRAY)
                ->getArrayResult();

            return array_map('intval', array_column($rows, 'cnt', 'language'));
        });

        return $this->translateLanguages($this->reduceResultSet(self::LIST_ROWS, $resultSet));
    }

    /**
     * The languages members chose for the site, by number of members; cached for an hour.
     */
    public function getPreferredLanguagesData(): array
    {
        $resultSet = $this->cache->get('statistics_preferred_languages', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            $rows = $this->entityManager
                ->createQuery('SELECT m.locale AS language, COUNT(m.id) AS cnt FROM ' . Member::class
                    . ' m WHERE m.status IN (:statuses) GROUP BY m.locale ORDER BY cnt DESC')
                ->setParameter('statuses', MemberStatusType::ACTIVE_ALL_ARRAY)
                ->getArrayResult();

            return array_map('intval', array_column($rows, 'cnt', 'language'));
        });

        return $this->translateLanguages($this->reduceResultSet(self::LIST_ROWS, $resultSet));
    }

    /**
     * The countries of the members' active addresses, by number of members; cached for an hour.
     */
    public function getMembersPerCountryData(): array
    {
        $resultSet = $this->cache->get('statistics_members_per_country', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            // Native SQL: the members per place first (a subquery in FROM, which DQL lacks), then per country;
            // that looks up each place once instead of once per address.
            $resultSet = $this->entityManager->getConnection()->fetchAllKeyValue(
                'SELECT l.country_id AS country, SUM(places.cnt) AS cnt FROM location l
                 INNER JOIN (
                     SELECT a.location, COUNT(*) AS cnt FROM address a INNER JOIN member m ON m.id = a.member_id
                     WHERE m.Status IN (:statuses) AND a.active = 1
                     GROUP BY a.location
                 ) places ON places.location = l.geoname_id
                 GROUP BY l.country_id
                 ORDER BY cnt DESC',
                ['statuses' => MemberStatusType::ACTIVE_ALL_ARRAY],
                ['statuses' => ArrayParameterType::STRING],
            );

            return array_map('intval', $resultSet);
        });

        return $this->translateCountries($this->reduceResultSet(self::LIST_ROWS, $resultSet));
    }

    /**
     * How long ago members were last active, in periods; cached for an hour.
     *
     * @SuppressWarnings("PHPMD")
     */
    public function getMembersPerLoginData(): array
    {
        $resultSet = $this->cache->get('statistics_logins', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            // Native SQL: TIMESTAMPDIFF counts whole days of 24 hours, DQL's DATE_DIFF calendar days
            return array_map('intval', $this->entityManager->getConnection()->fetchAllKeyValue(
                'SELECT TIMESTAMPDIFF(DAY, m.LastActive, NOW()) AS logindiff, COUNT(*) AS cnt
                 FROM member m
                 WHERE TIMESTAMPDIFF(DAY, m.LastActive, NOW()) >= 0 AND m.Status IN (:statuses)
                 GROUP BY logindiff
                 ORDER BY logindiff ASC',
                ['statuses' => MemberStatusType::ACTIVE_ALL_ARRAY],
                ['statuses' => ArrayParameterType::STRING],
            ));
        });

        $translator = $this->translator;
        $translatedPeriods = [
            '1 day' => $translator->trans('statistics.1day'),
            '1 week' => $translator->trans('statistics.1week'),
            '1-2 weeks' => $translator->trans('statistics.2weeks'),
            '2-4 weeks' => $translator->trans('statistics.4weeks'),
            '1-3 months' => $translator->trans('statistics.3months'),
            '3-6 months' => $translator->trans('statistics.6months'),
            '6-12 months' => $translator->trans('statistics.12months'),
            'longer' => $translator->trans('statistics.longer'),
        ];
        $result = [];
        $result['1 day'] = 0;
        $result['1 week'] = 0;
        $result['1-2 weeks'] = 0;
        $result['2-4 weeks'] = 0;
        $result['1-3 months'] = 0;
        $result['3-6 months'] = 0;
        $result['6-12 months'] = 0;
        $result['longer'] = 0;

        foreach ($resultSet as $diff => $count) {
            // 0: less than 24 hours ago
            if ($diff <= 1) {
                $result['1 day'] += $count;
            } elseif ($diff <= 7) {
                $result['1 week'] += $count;
            } elseif ($diff <= 14) {
                $result['1-2 weeks'] += $count;
            } elseif ($diff <= 28) {
                $result['2-4 weeks'] += $count;
            } elseif ($diff <= 91) {
                $result['1-3 months'] += $count;
            } elseif ($diff <= 182) {
                $result['3-6 months'] += $count;
            } elseif ($diff <= 365) {
                $result['6-12 months'] += $count;
            } else {
                $result['longer'] += $count;
            }
        }
        $translatedResult = [];
        foreach ($result as $key => $count) {
            $translatedResult[$translatedPeriods[$key]] = $count;
        }

        return $translatedResult;
    }

    /**
     * The first rows of a list, the others added up as one more row "Other (n)": at most $rows rows.
     */
    private function reduceResultSet(int $rows, array $resultSet): array
    {
        if (\count($resultSet) <= $rows) {
            return $resultSet;
        }
        $count = $rows - 1;
        $other = $this->translator->trans('statistics.other', ['count' => \count($resultSet) - $count]);
        $result = \array_slice($resultSet, 0, $count);
        $keys = array_keys($resultSet);
        $keyCount = \count($keys);
        for ($i = $count; $i < $keyCount; ++$i) {
            if (!isset($result[$other])) {
                $result[$other] = 0;
            }
            $result[$other] += $resultSet[$keys[$i]];
        }

        return $result;
    }

    private function setMemberInfo(string $current, Statistic $statistics): void
    {
        // Active members
        $statistics->setActiveMembers($this->count(
            'SELECT COUNT(m.id) FROM ' . Member::class . ' m WHERE m.status IN (:statuses) AND m.created <= :created',
            ['statuses' => self::DAILY_ACTIVE_STATUSES, 'created' => $current],
        ));

        // Number of member with at least one positive comment
        $statistics->setMembersWithPositiveComment($this->count(
            'SELECT COUNT(DISTINCT m.id) FROM ' . Comment::class . ' c JOIN c.toMember m'
            . ' WHERE m.status IN (:statuses) AND c.quality = :quality AND c.updated <= :updated',
            [
                'statuses' => self::DAILY_ACTIVE_STATUSES,
                'quality' => CommentQualityType::POSITIVE,
                'updated' => $current,
            ],
        ));
    }

    private function setLoggedInMembers(string $current, string $next, Statistic $statistics): void
    {
        // Number of members who have logged in during the current date
        $statistics->setMembersWhoLoggedInToday($this->count(
            'SELECT COUNT(m.id) FROM ' . Member::class . ' m'
            . ' WHERE m.status IN (:statuses) AND m.lastActive >= :current AND m.lastActive < :next',
            ['statuses' => self::DAILY_ACTIVE_STATUSES, 'current' => $current, 'next' => $next],
        ));
    }

    private function setMessagesSentAndRead(string $current, string $next, Statistic $statistics): void
    {
        // Number of messages sent from one member to another during the current date
        $statistics->setMessagesSent($this->count(
            'SELECT COUNT(m.id) FROM ' . Message::class . ' m'
            . ' WHERE m.created >= :current AND m.created < :next AND m.request IS NULL',
            ['current' => $current, 'next' => $next],
        ));

        // Number of messages read during the current date
        $statistics->setMessagesRead($this->count(
            'SELECT COUNT(m.id) FROM ' . Message::class . ' m'
            . ' WHERE m.firstRead >= :current AND m.firstRead < :next AND m.request IS NULL',
            ['current' => $current, 'next' => $next],
        ));
    }

    private function setRequestsSentAndAccepted(string $current, string $next, Statistic $statistics): void
    {
        // Number of requests created from one member to another during the current date
        $statistics->setRequestsSent($this->count(
            'SELECT COUNT(r.id) FROM ' . HostingRequest::class . ' r'
            . ' WHERE r.created >= :current AND r.created < :next AND r.inviteForLeg IS NULL',
            ['current' => $current, 'next' => $next],
        ));

        // Number of requests accepted during the current date
        $statistics->setRequestsAccepted($this->count(
            'SELECT COUNT(r.id) FROM ' . HostingRequest::class . ' r'
            . ' WHERE r.updated >= :current AND r.updated < :next AND r.status = :status AND r.inviteForLeg IS NULL',
            ['current' => $current, 'next' => $next, 'status' => HostingRequest::REQUEST_ACCEPTED],
        ));
    }

    private function setLegsCreated(string $current, string $next, Statistic $statistics): void
    {
        // Number of legs of the trips created during the current date
        $statistics->setLegsCreated($this->count(
            'SELECT COUNT(l.id) FROM ' . Leg::class . ' l JOIN l.trip t'
            . ' WHERE t.created >= :current AND t.created < :next',
            ['current' => $current, 'next' => $next],
        ));
    }

    private function setInvitationsSentAndAccepted(string $current, string $next, Statistic $statistics): void
    {
        // Number of invitations created from one member to another during the current date
        $statistics->setInvitationsSent($this->count(
            'SELECT COUNT(r.id) FROM ' . HostingRequest::class . ' r'
            . ' WHERE r.created >= :current AND r.created < :next AND r.inviteForLeg IS NOT NULL',
            ['current' => $current, 'next' => $next],
        ));

        // Number of invitations accepted during the current date
        $statistics->setInvitationsAccepted($this->count(
            'SELECT COUNT(r.id) FROM ' . HostingRequest::class . ' r'
            . ' WHERE r.updated >= :current AND r.updated < :next AND r.status = :status'
            . ' AND r.inviteForLeg IS NOT NULL',
            ['current' => $current, 'next' => $next, 'status' => HostingRequest::REQUEST_ACCEPTED],
        ));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function count(string $dql, array $parameters): int
    {
        return (int) $this->entityManager->createQuery($dql)->setParameters($parameters)->getSingleScalarResult();
    }

    private function prepareDailyData($data): array
    {
        $preparedData = [
            'labels' => [],
            'numbers' => [],
        ];

        foreach ($data as $datum) {
            $preparedData['labels'][] = $datum['day']->format('Y-m-d');
            $preparedData['numbers'][] = $datum['count'];
        }

        return $preparedData;
    }

    private function prepareWeeklyData($data): array
    {
        $preparedData = [
            'labels' => [],
            'numbers' => [],
        ];

        foreach ($data as $datum) {
            // turn provided yearweek into a date (first day of the week)
            $preparedData['labels'][] = date(
                'Y-m-d',
                strtotime(substr((string) $datum['week'], 0, 4)
                    . '-W' . substr((string) $datum['week'], 4, 2) . '-1')
            );
            $preparedData['numbers'][] = $datum['count'];
        }

        return $preparedData;
    }

    private function translateCountries(array $resultSet): array
    {
        $countryCodes = array_keys($resultSet);
        $qb = $this->entityManager->createQueryBuilder();
        $countriesQuery = $qb
            ->select('c')
            ->from(\App\Entity\Location::class, 'c', 'c.countryId')
            ->where($qb->expr()->in('c.countryId ', $countryCodes))
            ->andWhere($qb->expr()->eq('c.featureClass', $qb->expr()->literal('A')))
            ->andWhere($qb->expr()->eq('c.featureCode', $qb->expr()->literal('PCLI')))
            ->getQuery();
        $countriesQuery->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            TranslationWalker::class
        );
        $countriesQuery->setHint(
            TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            $this->translator->getLocale(),
        );
        // fallback
        $countriesQuery->setHint(
            TranslatableListener::HINT_FALLBACK,
            1 // fallback to default values in case if record is not translated
        );

        $countriesQuery->setMaxResults(50);
        $countries = $countriesQuery->getResult();

        $translatedCountries = [];
        foreach ($countryCodes as $key) {
            if (2 === \strlen((string) $key) && isset($countries[$key])) {
                $translatedCountries[$countries[$key]->getName()] = $resultSet[$key];
            } else {
                $translatedCountries[$key] = $resultSet[$key];
            }
        }

        return $translatedCountries;
    }

    private function translateLanguages(array $resultSet): array
    {
        $translatedLanguages = [];
        $languageCodes = array_keys($resultSet);
        foreach ($languageCodes as $key) {
            $translationId = 'lang_' . strtolower((string) $key);
            $languageName = $this->translator->trans($translationId);
            if ($translationId === $languageName) {
                $translatedLanguages[$key] = $resultSet[$key];
            } else {
                $translatedLanguages[$languageName] = $resultSet[$key];
            }
        }

        return $translatedLanguages;
    }
}
