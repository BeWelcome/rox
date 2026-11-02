<?php

namespace App\Model\Admin;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Repository\NewsletterStatisticsRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Mass mails (table broadcast) and their recipients (broadcastmessages) for the volunteers who send them.
 *
 * A recipient is enqueued (ToApprove), triggered (ToSend), then sent or failed by send:massmail. Subject and text
 * are the words broadcast_title_<name> and broadcast_body_<name>, translated like any other word. The counts per
 * status are kept in broadcast_statistics, changed with the messages.
 */
class MassmailModel
{
    public const string ENQUEUED = 'enqueued';
    public const string TRIGGERED = 'triggered';
    public const string SENT = 'sent';
    public const string FAILED = 'failed';

    /** About how many mails the cron sends per minute (send:massmail every minute); the pages estimate with it */
    public const int PER_MINUTE = 200;

    /** The stages of a mass mail (withState) */
    public const string STAGE_DRAFT = 'draft';
    public const string STAGE_ENQUEUED = 'enqueued';
    public const string STAGE_TRIGGERED = 'triggered';
    public const string STAGE_SENDING = 'sending';
    public const string STAGE_STOPPED = 'stopped';
    public const string STAGE_SENT = 'sent';

    /**
     * The counts of a mass mail (broadcast_statistics) and the statuses of the messages they count.
     */
    public const array STATUSES = [
        self::ENQUEUED => 'ToApprove',
        self::TRIGGERED => 'ToSend',
        self::SENT => 'Sent',
        self::FAILED => 'Failed',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly NewsletterStatisticsRepository $statistics,
    ) {
    }

    /**
     * The mass mails, newest first, with the number of recipients per status.
     */
    public function getPager(int $page, int $perPage): Pagerfanta
    {
        $pager = new Pagerfanta(new CallbackAdapter(
            fn (): int => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM broadcast'),
            fn (int $offset, int $length): array => array_map(
                fn (array $row): array => $this->withState($row),
                $this->connection->fetchAllAssociative(
                    'SELECT b.id, b.Name AS name, b.Type AS type, b.Status AS status, b.created, ' . $this->counts() . '
                    FROM broadcast b
                    LEFT JOIN broadcast_statistics s ON s.broadcast_id = b.id
                    ORDER BY b.created DESC, b.id DESC
                    LIMIT ' . $length . ' OFFSET ' . $offset,
                ),
            ),
        ));
        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage(max(1, min($page, $pager->getNbPages())));

        return $pager;
    }

    /**
     * A mass mail with the number of recipients per status and what can be done with it next.
     *
     * @return array<string, mixed>
     */
    public function get(Newsletter $newsletter): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT b.id, b.Name AS name, b.Type AS type, b.Status AS status, b.created, ' . $this->counts() . '
            FROM broadcast b
            LEFT JOIN broadcast_statistics s ON s.broadcast_id = b.id
            WHERE b.id = ?',
            [$newsletter->getId()],
        );

        return $this->withState((array) $row);
    }

    /**
     * The languages the text of the mass mail is translated into.
     *
     * @return list<array{code: string, name: string}>
     */
    public function getLanguages(Newsletter $newsletter): array
    {
        return array_map(
            static fn (array $row): array => ['code' => (string) $row['code'], 'name' => (string) $row['name']],
            $this->connection->fetchAllAssociative(
                'SELECT l.ShortCode AS code, l.Name AS name FROM word w JOIN language l ON l.ShortCode = w.ShortCode
                WHERE w.code = ? ORDER BY l.Name',
                ['broadcast_body_' . strtolower((string) $newsletter->getName())],
            ),
        );
    }

    public function wordCodeExists(string $name): bool
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM word WHERE code IN (?, ?)',
            ['broadcast_title_' . $name, 'broadcast_body_' . $name],
        ) > 0;
    }

    /**
     * The members to whom the mass mail goes: all who accept it by their preference, or only those named.
     *
     * @param list<string> $usernames
     */
    public function enqueueMembers(Newsletter $newsletter, Member $enqueuer, array $usernames, int $limit): int
    {
        $where = '';
        $parameters = [];
        $types = [];
        if ([] !== $usernames) {
            $where = ' AND m.Username IN (:usernames)';
            $parameters['usernames'] = $usernames;
            $types['usernames'] = ArrayParameterType::STRING;
        }

        return $this->enqueue(
            $newsletter,
            $enqueuer,
            'FROM member m ' . $this->preferenceJoin($newsletter) . ' WHERE ' . $this->accepts() . $where
                . ($limit > 0 ? ' LIMIT ' . $limit : ''),
            $parameters,
            $types,
        );
    }

    public function enqueueLocation(Newsletter $newsletter, Member $enqueuer, string $country, ?string $adminUnit, ?int $place): int
    {
        $where = ' AND g.country_id = :country';
        $parameters = ['country' => $country];
        if (null !== $adminUnit) {
            $where .= ' AND g.admin_1_id = :adminUnit';
            $parameters['adminUnit'] = $adminUnit;
        }
        if (null !== $place) {
            $where .= ' AND g.geoname_id = :place';
            $parameters['place'] = $place;
        }

        return $this->enqueue(
            $newsletter,
            $enqueuer,
            'FROM member m
            JOIN address a ON a.member_id = m.id AND a.active = 1
            JOIN location g ON g.geoname_id = a.location
            ' . $this->preferenceJoin($newsletter) . ' WHERE ' . $this->accepts() . $where,
            $parameters,
        );
    }

    public function enqueueGroup(Newsletter $newsletter, Member $enqueuer, int $group): int
    {
        return $this->enqueue(
            $newsletter,
            $enqueuer,
            'FROM group_membership gm
            JOIN member m ON m.id = gm.member_id
            ' . $this->preferenceJoin($newsletter) . " WHERE gm.group_id = :group AND gm.status = 'In' AND " . $this->accepts(),
            ['group' => $group],
        );
    }

    /**
     * A change of the terms goes to all members, whatever their preference.
     */
    public function enqueueTermsOfUse(Newsletter $newsletter, Member $enqueuer): int
    {
        return $this->enqueue(
            $newsletter,
            $enqueuer,
            'FROM member m WHERE m.Status IN (:statuses)',
            ['statuses' => MemberStatusType::ACTIVE_ALL_ARRAY],
            ['statuses' => ArrayParameterType::STRING],
        );
    }

    public function unqueue(Newsletter $newsletter): int
    {
        return $this->connection->transactional(function () use ($newsletter): int {
            $count = (int) $this->connection->executeStatement(
                "DELETE FROM broadcastmessages WHERE IdBroadcast = ? AND Status = 'ToApprove'",
                [$newsletter->getId()],
            );
            $this->statistics->add($newsletter, enqueued: -$count);

            return $count;
        });
    }

    public function trigger(Newsletter $newsletter): int
    {
        return $this->connection->transactional(function () use ($newsletter): int {
            $this->connection->executeStatement("UPDATE broadcast SET Status = 'Triggered' WHERE id = ?", [$newsletter->getId()]);
            $count = (int) $this->connection->executeStatement(
                "UPDATE broadcastmessages SET Status = 'ToSend', updated = NOW() WHERE IdBroadcast = ? AND Status = 'ToApprove'",
                [$newsletter->getId()],
            );
            $this->statistics->add($newsletter, enqueued: -$count, triggered: $count);

            return $count;
        });
    }

    /**
     * Stops the sending: the messages still waiting go back to the queue, the sent ones stay. Triggering again
     * (resume) sends only to those waiting, so nobody gets the mass mail twice. A message send:massmail is sending
     * right now is locked; the update waits for it and leaves it out once it is sent.
     *
     * @return int the number of messages moved back to the queue
     */
    public function untrigger(Newsletter $newsletter): int
    {
        return $this->connection->transactional(function () use ($newsletter): int {
            $this->connection->executeStatement("UPDATE broadcast SET Status = 'Created' WHERE id = ?", [$newsletter->getId()]);
            $count = (int) $this->connection->executeStatement(
                "UPDATE broadcastmessages SET Status = 'ToApprove', updated = NOW() WHERE IdBroadcast = ? AND Status = 'ToSend'",
                [$newsletter->getId()],
            );
            $this->statistics->add($newsletter, enqueued: $count, triggered: -$count);

            return $count;
        });
    }

    /**
     * The countries to choose from (all, or those of the volunteer's scope).
     *
     * @param list<string> $only
     *
     * @return array<string, string> name => country code
     */
    public function getCountries(array $only): array
    {
        $where = "feature_class = 'A' AND feature_code LIKE 'PCL%' AND feature_code <> 'PCLH'";
        $parameters = [];
        $types = [];
        if ([] !== $only) {
            $where .= ' AND country_id IN (:only)';
            $parameters['only'] = $only;
            $types['only'] = ArrayParameterType::STRING;
        }

        return $this->connection->fetchAllKeyValue(
            'SELECT name, country_id FROM location WHERE ' . $where . ' ORDER BY name',
            $parameters,
            $types,
        );
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getAdminUnits(string $country): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT admin_1_id AS id, name FROM location WHERE country_id = ? AND feature_code = 'ADM1' ORDER BY name",
            [$country],
        );
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function getPlaces(string $country, string $adminUnit): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT geoname_id AS id, name FROM location
            WHERE country_id = ? AND admin_1_id = ? AND feature_class = 'P' AND feature_code <> 'PPLX'
            ORDER BY name",
            [$country, $adminUnit],
        );
    }

    /**
     * The groups to choose from (all, or those of the volunteer's scope).
     *
     * @param list<int> $only
     *
     * @return array<string, int> name => id
     */
    public function getGroups(array $only): array
    {
        $parameters = [];
        $types = [];
        $where = '';
        if ([] !== $only) {
            $where = ' WHERE id IN (:only)';
            $parameters['only'] = $only;
            $types['only'] = ArrayParameterType::INTEGER;
        }

        return array_map(intval(...), $this->connection->fetchAllKeyValue(
            'SELECT name, id FROM `group`' . $where . ' ORDER BY name',
            $parameters,
            $types,
        ));
    }

    /**
     * @param array<string, mixed>                     $parameters
     * @param array<string, ArrayParameterType|string> $types
     */
    /**
     * Members who already get the mass mail aren't enqueued again.
     */
    private function enqueue(Newsletter $newsletter, Member $enqueuer, string $from, array $parameters, array $types = []): int
    {
        return $this->connection->transactional(function () use ($newsletter, $enqueuer, $from, $parameters, $types): int {
            $count = (int) $this->connection->executeStatement(
                "INSERT IGNORE INTO broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
                SELECT :newsletter, m.id, :enqueuer, 'ToApprove', NOW() " . $from,
                $parameters + ['newsletter' => $newsletter->getId(), 'enqueuer' => $enqueuer->getId()],
                $types,
            );
            $this->statistics->add($newsletter, enqueued: $count);

            return $count;
        });
    }

    /**
     * Local events go to those who want them, newsletters to those who accept news by mail (both by default).
     */
    private function preferenceJoin(Newsletter $newsletter): string
    {
        $preference = Newsletter::SPECIFIC_NEWSLETTER === $newsletter->getType() ? 'PreferenceLocalEvent' : 'PreferenceAcceptNewsByMail';

        return "LEFT JOIN memberspreferences mp ON mp.IdMember = m.id
            AND mp.IdPreference = (SELECT p.id FROM preferences p WHERE p.codeName = '" . $preference . "')";
    }

    private function accepts(): string
    {
        return 'm.Status IN (' . MemberStatusType::ACTIVE_WITH_MESSAGES . ") AND (mp.Value = 'Yes' OR mp.Value IS NULL)";
    }

    private function counts(): string
    {
        $counts = [];
        foreach (array_keys(self::STATUSES) as $name) {
            $counts[] = 'COALESCE(s.' . $name . ', 0) AS ' . $name;
        }

        return implode(', ', $counts);
    }

    /**
     * The stage of a mass mail and what can be done next. A stopped mass mail sent some messages and has others in
     * the queue again; resuming it triggers those. A mass mail whose recipients were all skipped by send:massmail
     * (left or suspended since) has no counts but the status Triggered: it counts as sent.
     *
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function withState(array $row): array
    {
        foreach (array_keys(self::STATUSES) as $name) {
            $row[$name] = (int) ($row[$name] ?? 0);
        }
        $done = $row[self::SENT] > 0 || $row[self::FAILED] > 0;
        $stage = match (true) {
            $row[self::TRIGGERED] > 0 => $done ? self::STAGE_SENDING : self::STAGE_TRIGGERED,
            $row[self::ENQUEUED] > 0 => $done ? self::STAGE_STOPPED : self::STAGE_ENQUEUED,
            $done, 'Triggered' === ($row['status'] ?? null) => self::STAGE_SENT,
            default => self::STAGE_DRAFT,
        };
        // the types that were enqueued for reminders and notices (RemindToLog, SuspendAfter5Reminders, ...) are sent
        // by the member commands now
        $sendable = \in_array($row['type'] ?? null, [Newsletter::REGULAR_NEWSLETTER, Newsletter::SPECIFIC_NEWSLETTER, Newsletter::TERMS_OF_USE], true);
        $waiting = \in_array($stage, [self::STAGE_ENQUEUED, self::STAGE_STOPPED], true);
        $sending = \in_array($stage, [self::STAGE_TRIGGERED, self::STAGE_SENDING], true);

        return $row + [
            'stage' => $stage,
            'progress' => $this->progress($stage, $row),
            // the messages the next step sends: those waiting in the queue, or those being sent
            'remaining' => $sending ? $row[self::TRIGGERED] : ($waiting ? $row[self::ENQUEUED] : 0),
            'can_edit' => self::STAGE_DRAFT === $stage,
            'can_enqueue' => self::STAGE_DRAFT === $stage && $sendable,
            'can_unqueue' => $waiting,
            'can_trigger' => $waiting,
            'can_untrigger' => $sending,
        ];
    }

    /**
     * How far the mass mail is, in percent: a quarter per step; while sending (or stopped), the sent share of the
     * last quarter.
     *
     * @param array<string, mixed> $row
     */
    private function progress(string $stage, array $row): int
    {
        if (\in_array($stage, [self::STAGE_SENDING, self::STAGE_STOPPED], true)) {
            $done = $row[self::SENT] + $row[self::FAILED];
            $all = $done + $row[self::TRIGGERED] + $row[self::ENQUEUED];

            return 62 + intdiv(38 * $done, max(1, $all));
        }

        return [self::STAGE_DRAFT => 12, self::STAGE_ENQUEUED => 37, self::STAGE_TRIGGERED => 62][$stage] ?? 100;
    }
}
