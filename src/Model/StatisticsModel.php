<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\HostingRequest;
use App\Entity\Statistic;
use App\Utilities\ManagerTrait;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use PDO;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use vendor\project\StatusTest;

class StatisticsModel
{
    use ManagerTrait;

    public function getStatistics()
    {
        $connection = $this->getManager()->getConnection();

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
            'countries' => count($countries),
            'languages' => $languages['cnt'],
            'comments' => $positiveComments['cnt'],
            'activities' => $activities['cnt'],
        ];

        return $stats;
    }

    /**
     * @param DatePeriod $dates
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
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

            $em->persist($statistics);
        }
        if ($progressBar) {
            // ensures that the progress bar is at 100%
            $progressBar->finish();
        }

        $em->flush();
        return 0;
    }

    /**
     * @param Connection $connection
     * @param string $current
     * @param Statistic $statistics
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
     * @param Connection $connection
     * @param string $current
     * @param string $next
     * @param Statistic $statistics
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
     * @param Connection $connection
     * @param string $current
     * @param string $next
     * @param Statistic $statistics
     * @throws DBALException
     */
    private function setMessagesSentAndRead(Connection $connection, string $current, string $next, $statistics): void
    {
        // Number of messages sent from one member to another during the current date
        $result = $connection->executeQuery(
            "
                    SELECT
                      COUNT(m.id) AS cnt
                    FROM
                      messages m
                    WHERE
                      m.DateSent >= :current
                      AND m.DateSent < :next
                      AND m.request_id IS null
                      ",
            [
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setMessagesSent($result['cnt']);

        // Number of messages read during the current date
        $result = $connection->executeQuery(
            "
                    SELECT
                      COUNT(m.id) AS cnt
                    FROM
                      messages m
                    WHERE
                      m.WhenFirstRead >= :current
                      AND m.WhenFirstRead < :next
                      AND m.request_id IS null
                      ",
            [
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setMessagesRead($result['cnt']);
    }

    /**
     * @param Connection $connection
     * @param string $current
     * @param string $next
     * @param Statistic $statistics
     * @throws DBALException
     */
    private function setRequestsSentAndAccepted(Connection $connection, string $current, string $next, $statistics): void
    {
        // Number of requests created from one member to another during the current date
        $result = $connection->executeQuery(
            "
                    SELECT
                      COUNT(r.id) AS cnt
                    FROM
                      request r
                    WHERE
                      r.created >= :current
                      AND r.created < :next
                      ",
            [
                ':current' => $current,
                ':next' => $next,
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setRequestsSent($result['cnt']);

        // Number of requests accepted during the current date
        $result = $connection->executeQuery(
            "
                    SELECT
                      COUNT(r.id) AS cnt
                    FROM
                      request r
                    WHERE
                      r.updated >= :current
                      AND r.updated < :next
                      AND r.`status` = :status
                      ",
            [
                ':current' => $current,
                ':next' => $next,
                ':status' => HostingRequest::REQUEST_ACCEPTED
            ]
        )
            ->fetch(PDO::FETCH_ASSOC);
        $statistics->setRequestsAccepted($result['cnt']);
    }
}
