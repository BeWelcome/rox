<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\HostingRequest;
use App\Entity\Statistic;
use App\Repository\StatisticsRepository;
use App\Utilities\ManagerTrait;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use PDO;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatisticsModel
{
    use ManagerTrait;

    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public function getStatisticsHomepage()
    {
        $connection = $this->entityManager->getConnection();

        $members = $connection->executeQuery('
            SELECT
                COUNT(*) AS cnt
            FROM
                members m
            WHERE
                m.status IN (' . MemberStatusType::ACTIVE_ALL . ')
        ')->fetch();

        $countries = $connection->executeQuery("
            SELECT
                DISTINCT gc.country
            FROM
                geonamescountries gc
                join geonames g on gc.country = g.country
                join members m on g.geonameId = m.IdCity and m.Status IN ('Active', 'OutOfRemind')
        ")->fetchAll();

        $languages = $connection->executeQuery('
            SELECT
                COUNT(DISTINCT l.id) AS cnt
            FROM
                languages l,
                memberslanguageslevel mll,
                members m
            WHERE
                l.id = mll.idLanguage
                AND mll.IdMember = m.Id
                AND m.Status IN (' . MemberStatusType::ACTIVE_ALL . ')
        ')->fetch();

        $positiveComments = $connection->executeQuery("
            SELECT
                COUNT(c.id) AS cnt
            FROM
                comments c,
                members m
            WHERE
                c.Quality = 'Good'
                AND IdFromMember = m.Id
                AND m.Status IN (" . MemberStatusType::ACTIVE_ALL . ')
        ')->fetch();

        $activities = $connection->executeQuery('
            SELECT
                COUNT(a.id) AS cnt
            FROM
                activities a
            WHERE
                a.status = 0
        ')->fetch();

        $stats = [
            'members' => $members['cnt'],
            'countries' => \count($countries),
            'languages' => $languages['cnt'],
            'comments' => $positiveComments['cnt'],
            'activities' => $activities['cnt'],
        ];

        return $stats;
    }

    /**
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return int
     */
    public function updateStatistics(DatePeriod $dates, OutputInterface $output)
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

        $em = $this->getManager();
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
            $this->setInvititationsSentAndAccepted($connection, $current, $next, $statistics);

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
        $result = $connection->executeQuery("
            SELECT
                l.englishname language,
                COUNT(m.id) cnt
            FROM
                memberslanguageslevel mll,
                languages l,
                members m
            WHERE
                l.id = mll.IdLanguage
                AND mll.idMember = m.id
                AND m.Status IN (" . MemberStatusType::ACTIVE_ALL . ")
            GROUP BY
                l.name
            ORDER BY
                cnt DESC
        ");

        return $this->reduceResultSet(10, $result->fetchAllKeyValue());
    }

    public function getPreferredLanguagesData(): array
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery("
            SELECT
                l.englishname language,
                COUNT(m.id) cnt
            FROM
                languages l, `members` m
            LEFT JOIN
                memberspreferences mp
            ON
                m.id = mp.idmember
                AND mp.idpreference = 1
            WHERE
                m.status IN (" . MemberStatusType::ACTIVE_ALL . ")
                AND l.id = IFNULL(mp.value, 0)
            GROUP BY
                language
            ORDER BY
                cnt DESC
        ");

        return $this->reduceResultSet(14, $result->fetchAllKeyValue());
    }

    public function getMembersPerCountryData(): array
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery("
            SELECT
                gc.name AS countryname,
                count(*) AS cnt
            FROM
                members m,
                geonamescountries gc,
                geonames g
            WHERE
                m.Status IN (" . MemberStatusType::ACTIVE_ALL . ")
                AND
                m.IdCity = g.geonameId
                AND
                g.country = gc.country
            GROUP BY
                gc.country
            ORDER BY
                cnt DESC
        ");

        return $this->reduceResultSet(14, $result->fetchAllKeyValue());
    }

    public function getMembersPerLoginData(): array
    {
        $connection = $this->entityManager->getConnection();
        $executionResult = $connection->executeQuery("
            SELECT
                TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff,
                COUNT(*) AS cnt
            FROM members
            WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
            AND status IN (" . MemberStatusType::ACTIVE_ALL . ")
            GROUP BY logindiff
            ORDER BY logindiff ASC
        ");

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
            if ($diff == 1) {
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
        $other = $this->translator->trans('statistics.other', [ 'count' => count($resultSet) - $count + 1]);
        $result = array_slice($resultSet, 0, $count);
        $keys = array_keys($resultSet);
        for($i = $count; $i < count($keys); $i++) {
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
        $result = $connection->executeQuery(
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
                ':created' => $current,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setActiveMembers($result['cnt']);

        // Number of member with at least one positive comment
        $result = $connection->executeQuery(
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
                ':updated' => $current,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setMembersWithPositiveComment($result['cnt']);
    }

    /**
     * @param Statistic $statistics
     *
     * @throws DBALException
     */
    private function setLoggedInMembers(Connection $connection, string $current, string $next, $statistics): void
    {
        // Number of member who have logged in during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setMembersWhoLoggedInToday($result['cnt']);
    }

    /**
     * @param Statistic $statistics
     *
     * @throws DBALException
     */
    private function setMessagesSentAndRead(Connection $connection, string $current, string $next, $statistics): void
    {
        // Number of messages sent from one member to another during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setMessagesSent($result['cnt']);

        // Number of messages read during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setMessagesRead($result['cnt']);
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
        $statistics
    ): void {
        // Number of requests created from one member to another during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setRequestsSent($result['cnt']);

        // Number of requests accepted during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
                ':status' => HostingRequest::REQUEST_ACCEPTED,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setRequestsAccepted($result['cnt']);
    }

    /**
     * @throws DBALException
     */
    private function setLegsCreated(
        Connection $connection,
        string $current,
        string $next,
        Statistic $statistics
    ): void {
        // Number of requests created from one member to another during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setLegsCreated($result['cnt']);
    }

    /**
     * @throws DBALException
     */
    private function setInvititationsSentAndAccepted(
        Connection $connection,
        string $current,
        string $next,
        Statistic $statistics
    ): void {
        // Number of requests created from one member to another during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setInvitationsSent($result['cnt']);

        // Number of requests accepted during the current date
        $result = $connection->executeQuery(
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
                ':current' => $current,
                ':next' => $next,
                ':status' => HostingRequest::REQUEST_ACCEPTED,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setInvitationsAccepted($result['cnt']);
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
                strtotime(substr($datum['week'], 0, 4)
                    . '-W' . substr($datum['week'], 4, 2) . '-1')
            );
            $preparedData['numbers'][] = $datum['count'];
        }

        return $preparedData;
    }
}
