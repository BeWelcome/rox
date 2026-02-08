<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\HostingRequest;
use App\Entity\Statistic;
use App\Repository\StatisticsRepository;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 *
 * \todo Move statistics of different aspects  into different models
 */
class StatisticsModel
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getStatisticsHomepage(): array
    {
        $connection = $this->entityManager->getConnection();

        $members = $connection->executeQuery('
            SELECT
                COUNT(*) AS cnt
            FROM
                member m
            WHERE
                m.status IN (' . MemberStatusType::ACTIVE_ALL . ')
        ')->fetchOne();

        $countries = $connection->executeQuery("
            SELECT
                COUNT(DISTINCT g.country_id) AS cnt
            FROM
                member m,
			    address a
            JOIN geo__names g ON a.location = g.geoname_id 
			WHERE m.id = a.member_id and m.Status IN ('Active', 'OutOfRemind')
        ")->fetchOne();

        $languages = $connection->executeQuery('
            SELECT
                COUNT(DISTINCT l.shortCode) AS cnt
            FROM
                language l,
                member_language_level mll,
                member m
            WHERE
                l.ShortCode = mll.language
                AND mll.member_id = m.Id
                AND m.Status IN (' . MemberStatusType::ACTIVE_ALL . ')
        ')->fetchOne();

        $positiveComments = $connection->executeQuery("
            SELECT
                COUNT(c.id) AS cnt
            FROM
                comments c,
                member m
            WHERE
                c.Quality = 'Good'
                AND IdFromMember = m.Id
                AND m.Status IN (" . MemberStatusType::ACTIVE_ALL . ')
        ')->fetchOne();

        $activities = $connection->executeQuery('
            SELECT
                COUNT(a.id)
            FROM
                activities a
            WHERE
                a.status = 0
        ')->fetchOne();

        $stats = [
            'members' => $members,
            'countries' => $countries,
            'languages' => $languages,
            'comments' => $positiveComments,
            'activities' => $activities,
        ];

        return $stats;
    }

    /**
     * @throws DBALException
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws OptimisticLockException
     */
    public function updateStatistics(DatePeriod $dates, OutputInterface $output): int
    {
        $progressBar = null;
        $count = iterator_count($dates);
        $output->write(['Update statistics ']);
        if (1 !== $count) {
            $output->writeln(['from ' . $dates->getStartDate()->format('Y-m-d')
                . ' to ' . $dates->getEndDate()->format('Y-m-d'),
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
        $connection = $em->getConnection();
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
            $this->setMemberInfo($connection, $current, $statistics);

            if ($new) {
                $this->setLoggedInMembers($connection, $current, $next, $statistics);
            }
            $this->setMessagesSentAndRead($connection, $current, $next, $statistics);
            $this->setRequestsSentAndAccepted($connection, $current, $next, $statistics);
            $this->setLegsCreated($connection, $current, $next, $statistics);
            $this->setInvitationsSentAndAccepted($connection, $current, $next, $statistics);

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

    public function getLanguagesData(): array
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery('
            SELECT
                l.shortCode AS `language`,
                COUNT(m.id) cnt
            FROM
                member_language_level mll,
                languages l,
                members m
            WHERE
                l.shortCode = mll.shortCode
                AND mll.member_id = m.id
                AND m.Status IN (' . MemberStatusType::ACTIVE_ALL . ')
            GROUP BY
                l.name
            ORDER BY
                cnt DESC
        ');

        $resultSet = $this->reduceResultSet(10, $result->fetchAllKeyValue());

        return $this->translateLanguages($resultSet);
    }

    public function getPreferredLanguagesData(): array
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery('
            SELECT
                l.shortCode language,
                COUNT(m.id) cnt
            FROM
                languages l, `members` m
            LEFT JOIN
                memberspreferences mp
            ON
                m.id = mp.idmember
                AND mp.idpreference = 1
            WHERE
                m.status IN (' . MemberStatusType::ACTIVE_ALL . ')
                AND l.ShortCode = IFNULL(mp.value, \'en\')
            GROUP BY
                language
            ORDER BY
                cnt DESC
        ');

        $resultSet = $this->reduceResultSet(14, $result->fetchAllKeyValue());

        return $this->translateLanguages($resultSet);
    }

    public function getMembersPerCountryData(): array
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery('
            SELECT
                gc.country AS country,
                count(*) AS cnt
            FROM
                members m,
                address a,
                geo__names g
            WHERE
                m.Status IN (' . MemberStatusType::ACTIVE_ALL . ')
                AND m.id = a.member_id 
                AND a.active = 1
                AND a.location = g.geoname_id
            GROUP BY
                g.country
            ORDER BY
                cnt DESC
        ');

        $resultSet = $this->reduceResultSet(14, $result->fetchAllKeyValue());

        return $this->translateCountries($resultSet);
    }

    /**
     * @SuppressWarnings("PHPMD")
     */
    public function getMembersPerLoginData(): array
    {
        $connection = $this->entityManager->getConnection();
        $executionResult = $connection->executeQuery('
            SELECT
                TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff,
                COUNT(*) AS cnt
            FROM members
            WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
            AND status IN (' . MemberStatusType::ACTIVE_ALL . ')
            GROUP BY logindiff
            ORDER BY logindiff ASC
        ');

        $resultSet = $executionResult->fetchAllKeyValue();

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
            if (1 === $diff) {
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

    private function reduceResultSet(int $count, array $resultSet): array
    {
        $other = $this->translator->trans('statistics.other', ['count' => \count($resultSet) - $count + 1]);
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

    /**
     * @param Statistic $statistics
     *
     * @throws DBALException
     */
    private function setMemberInfo(Connection $connection, string $current, $statistics): void
    {
        // Active members
        $count = $connection->executeQuery(
            "
                    SELECT
                      COUNT(*) AS cnt
                    FROM
                      members m
                    WHERE
                      m.`Status` IN ('Active','ChoiceInactive','OutOfRemind')
                      AND m.created <= :created
                ",
            [
                'created' => $current,
            ]
        )
            ->fetchOne();
        $statistics->setActiveMembers($count);

        // Number of member with at least one positive comment
        $count = $connection->executeQuery(
            "
                    SELECT
                      COUNT(DISTINCT(m.id)) AS cnt
                    FROM
                      members m,
                      comments c
                    WHERE
                      m.`Status` IN ('Active','ChoiceInactive','OutOfRemind')
                      AND m.id=c.IdToMember
                      AND c.Quality='Good'
                      AND c.updated <= :updated
                      ",
            [
                'updated' => $current,
            ]
        )
            ->fetchOne();
        $statistics->setMembersWithPositiveComment($count);
    }

    /**
     * @param Statistic $statistics
     *
     * @throws DBALException
     */
    private function setLoggedInMembers(Connection $connection, string $current, string $next, $statistics): void
    {
        // Number of member who have logged in during the current date
        $count = $connection->executeQuery(
            "
                        SELECT
                          COUNT(m.id) AS cnt
                        FROM
                          members m
                        WHERE
                          m.`Status` IN ('Active','ChoiceInactive','OutOfRemind')
                          AND m.LastLogin >= :current
                          AND m.LastLogin < :next
                          ",
            [
                'current' => $current,
                'next' => $next,
            ]
        )
            ->fetchOne();
        $statistics->setMembersWhoLoggedInToday($count);
    }

    /**
     * @param Statistic $statistics
     *
     * @throws DBALException
     */
    private function setMessagesSentAndRead(Connection $connection, string $current, string $next, $statistics): void
    {
        // Number of messages sent from one member to another during the current date
        $count = $connection->executeQuery(
            '
                    SELECT
                      COUNT(m.id) AS cnt
                    FROM
                      messages m
                    WHERE
                      m.DateSent >= :current
                      AND m.DateSent < :next
                      AND m.request_id IS null
                      ',
            [
                'current' => $current,
                'next' => $next,
            ]
        )
            ->fetchOne();
        $statistics->setMessagesSent($count);

        // Number of messages read during the current date
        $count = $connection->executeQuery(
            '
                    SELECT
                      COUNT(m.id) AS cnt
                    FROM
                      messages m
                    WHERE
                      m.WhenFirstRead >= :current
                      AND m.WhenFirstRead < :next
                      AND m.request_id IS null
                      ',
            [
                'current' => $current,
                'next' => $next,
            ]
        )
            ->fetchOne();
        $statistics->setMessagesRead($count);
    }

    /**
     * @param Statistic $statistics
     *
     * @throws DBALException
     */
    private function setRequestsSentAndAccepted(
        Connection $connection,
        string $current,
        string $next,
        $statistics,
    ): void {
        // Number of requests created from one member to another during the current date
        $count = $connection->executeQuery(
            '
                    SELECT
                      COUNT(r.id) AS cnt
                    FROM
                      request r
                    WHERE
                      r.created >= :current
                      AND r.created < :next
                      AND r.invite_for_leg IS NULL
                      ',
            [
                'current' => $current,
                'next' => $next,
            ]
        )
            ->fetchOne();
        $statistics->setRequestsSent($count);

        // Number of requests accepted during the current date
        $count = $connection->executeQuery(
            '
                    SELECT
                      COUNT(r.id) AS cnt
                    FROM
                      request r
                    WHERE
                      r.updated >= :current
                      AND r.updated < :next
                      AND r.`status` = :status
                      AND r.invite_for_leg IS NULL
                      ',
            [
                'current' => $current,
                'next' => $next,
                'status' => HostingRequest::REQUEST_ACCEPTED,
            ]
        )
            ->fetchOne();
        $statistics->setRequestsAccepted($count);
    }

    /**
     * @throws DBALException
     */
    private function setLegsCreated(
        Connection $connection,
        string $current,
        string $next,
        Statistic $statistics,
    ): void {
        // Number of requests created from one member to another during the current date
        $count = $connection->executeQuery(
            '
                    SELECT
                      COUNT(s.id) AS cnt
                    FROM
                      sub_trips s
                    JOIN
                        trips t ON s.trip_id = t.id
                    WHERE
                      t.created >= :current
                      AND t.created < :next
                      ',
            [
                'current' => $current,
                'next' => $next,
            ]
        )
            ->fetchOne();
        $statistics->setLegsCreated($count);
    }

    /**
     * @throws DBALException
     */
    private function setInvitationsSentAndAccepted(
        Connection $connection,
        string $current,
        string $next,
        Statistic $statistics,
    ): void {
        // Number of requests created from one member to another during the current date
        $count = $connection->executeQuery(
            '
                    SELECT
                      COUNT(r.id) AS cnt
                    FROM
                      request r
                    WHERE
                      r.created >= :current
                      AND r.created < :next
                      AND NOT r.invite_for_leg IS NULL
                      ',
            [
                'current' => $current,
                'next' => $next,
            ]
        )
            ->fetchOne();
        $statistics->setInvitationsSent($count);

        // Number of requests accepted during the current date
        $count = $connection->executeQuery(
            '
                    SELECT
                      COUNT(r.id) AS cnt
                    FROM
                      request r
                    WHERE
                      r.updated >= :current
                      AND r.updated < :next
                      AND r.`status` = :status
                      AND NOT r.invite_for_leg IS NULL
                      ',
            [
                'current' => $current,
                'next' => $next,
                'status' => HostingRequest::REQUEST_ACCEPTED,
            ]
        )
            ->fetchOne();
        $statistics->setInvitationsAccepted($count);
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
