<?php

namespace App\Model\Admin;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Carbon\Carbon;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Pagerfanta;

/**
 * The members who signed up in the last 90 days, for the volunteers who greet them.
 *
 * member.bewelcomed holds which greetings were sent: 1 the local one, 2 the global one. The entity maps the column
 * as a boolean, so it is read and written here directly.
 */
class NewMembersModel
{
    public const int LOCAL_GREETING = 1;
    public const int GLOBAL_GREETING = 2;

    /** The profile filter: everybody, a photo or text, neither */
    public const string PROFILE_ALL = 'all';
    public const string PROFILE_FULL = 'full';
    public const string PROFILE_EMPTY = 'empty';
    public const array PROFILES = [self::PROFILE_ALL, self::PROFILE_FULL, self::PROFILE_EMPTY];

    private const int DAYS = 90;
    /** neither a profile picture nor a text about them */
    private const string EMPTY_PROFILE = "NOT EXISTS (SELECT 1 FROM member_photo p WHERE p.member_id = m.id) AND TRIM(COALESCE(m.AboutMe, '')) = ''";
    /** the member's country, from the active address */
    private const string FROM = 'member m
            LEFT JOIN address a ON a.member_id = m.id AND a.active = 1
            LEFT JOIN location g ON g.geoname_id = a.location
            LEFT JOIN location c ON c.geoname_id = g.country';
    private const int SUMMARY_WORDS = 70;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * With $all (the safety team and checkers) the unfinished signups and the members greeted twice as well,
     * oldest first; else the newest first. Only from the countries (geoname ids; none: everywhere) and with the
     * profile (PROFILES).
     *
     * @param list<int> $countries
     */
    public function getPager(bool $all, string $locale, int $page, int $perPage, array $countries = [], string $profile = self::PROFILE_ALL): Pagerfanta
    {
        $pager = new Pagerfanta(new CallbackAdapter(
            fn (): int => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM ' . self::FROM . ' WHERE ' . $this->where($all, $countries, $profile), $this->parameters($all, $countries), $this->types()),
            fn (int $offset, int $length): array => $this->members($all, $locale, $offset, $length, $countries, $profile),
        ));
        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage(max(1, min($page, $pager->getNbPages())));

        return $pager;
    }

    /**
     * The countries the members of the list live in, with how many each (whatever the country filter), by name.
     *
     * @return list<array{id: int, name: string, count: int}>
     */
    public function getCountries(bool $all, string $locale, string $profile = self::PROFILE_ALL): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT c.geoname_id AS id, ' . $this->placeName('c') . ' AS name, COUNT(*) AS count
            FROM ' . self::FROM . '
            WHERE c.geoname_id IS NOT NULL AND ' . $this->where($all, [], $profile) . '
            GROUP BY c.geoname_id, c.name
            ORDER BY name',
            $this->parameters($all, []) + ['locale' => $locale],
            $this->types(),
        );

        return array_map(static fn (array $row): array => ['id' => (int) $row['id'], 'name' => (string) $row['name'], 'count' => (int) $row['count']], $rows);
    }

    /**
     * How many members each profile filter shows (in the countries).
     *
     * @param list<int> $countries
     *
     * @return array<string, int> by PROFILES
     */
    public function getProfileCounts(bool $all, array $countries): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS total, SUM(' . self::EMPTY_PROFILE . ') AS empty FROM ' . self::FROM . ' WHERE ' . $this->where($all, $countries, self::PROFILE_ALL),
            $this->parameters($all, $countries),
            $this->types(),
        ) ?: ['total' => 0, 'empty' => 0];

        return [
            self::PROFILE_ALL => (int) $row['total'],
            self::PROFILE_FULL => (int) $row['total'] - (int) $row['empty'],
            self::PROFILE_EMPTY => (int) $row['empty'],
        ];
    }

    /**
     * How many members still wait for a greeting (one of the two isn't sent).
     */
    public function countWaiting(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM member m WHERE ' . $this->where(false, [], self::PROFILE_ALL), $this->parameters(false, []), $this->types());
    }

    public function markGreetingSent(Member $member, int $greeting): void
    {
        $this->connection->executeStatement(
            'UPDATE member SET bewelcomed = bewelcomed | ? WHERE id = ?',
            [$greeting, $member->getId()],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    /**
     * @param list<int> $countries
     */
    private function members(bool $all, string $locale, int $offset, int $length, array $countries, string $profile): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT m.id, m.Username AS username, m.created, m.Status AS status, m.Email AS email,
                m.bewelcomed, m.AboutMe AS about_me, ' . $this->placeName('g') . ' AS place, ' . $this->placeName('c') . ' AS country,
                EXISTS (SELECT 1 FROM member_photo p WHERE p.member_id = m.id) AS has_photo
            FROM ' . self::FROM . '
            WHERE ' . $this->where($all, $countries, $profile) . '
            ORDER BY m.created ' . ($all ? 'ASC' : 'DESC') . '
            LIMIT ' . $length . ' OFFSET ' . $offset,
            $this->parameters($all, $countries) + ['locale' => $locale],
            $this->types(),
        );

        $languages = $this->languages(array_map(static fn (array $row): int => (int) $row['id'], $rows));

        return array_map(fn (array $row): array => $row + [
            'summary' => $this->summary((string) $row['about_me']),
            'local_greeting_sent' => 0 !== ((int) $row['bewelcomed'] & self::LOCAL_GREETING),
            'global_greeting_sent' => 0 !== ((int) $row['bewelcomed'] & self::GLOBAL_GREETING),
            'languages' => $languages[(int) $row['id']] ?? [],
            'signed_up' => new Carbon((string) $row['created']),
            'has_photo' => (bool) $row['has_photo'],
            'has_text' => '' !== trim((string) $row['about_me']),
        ], $rows);
    }

    /**
     * @param list<int> $ids
     *
     * @return array<int, list<array{language: string, level: string}>>
     */
    private function languages(array $ids): array
    {
        if ([] === $ids) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT member_id, language, level FROM member_language_level WHERE member_id IN (:ids)
            ORDER BY FIELD(level, 'mother.tongue', 'expert', 'fluent', 'intermediate', 'beginner', 'hello.only')",
            ['ids' => $ids],
            ['ids' => ArrayParameterType::INTEGER],
        );
        $languages = [];
        foreach ($rows as $row) {
            $languages[(int) $row['member_id']][] = ['language' => (string) $row['language'], 'level' => (string) $row['level']];
        }

        return $languages;
    }

    /** place and country in the viewer's language, else in English, else as GeoNames names them */
    private function placeName(string $alias): string
    {
        return "COALESCE(
            (SELECT t.content FROM location_translation t WHERE t.object_id = {$alias}.geoname_id AND t.field = 'name'
                AND t.locale IN (:locale, 'en') ORDER BY t.locale = :locale DESC LIMIT 1),
            {$alias}.name)";
    }

    /**
     * @param list<int> $countries
     */
    private function where(bool $all, array $countries = [], string $profile = self::PROFILE_ALL): string
    {
        return 'm.Status IN (:statuses) AND m.created > NOW() - INTERVAL ' . self::DAYS . ' DAY'
            . ($all ? '' : ' AND m.bewelcomed < ' . (self::LOCAL_GREETING | self::GLOBAL_GREETING))
            . ([] === $countries ? '' : ' AND c.geoname_id IN (:countries)')
            . match ($profile) {
                self::PROFILE_EMPTY => ' AND ' . self::EMPTY_PROFILE,
                self::PROFILE_FULL => ' AND NOT (' . self::EMPTY_PROFILE . ')',
                default => '',
            };
    }

    /**
     * @param list<int> $countries
     *
     * @return array<string, mixed>
     */
    private function parameters(bool $all, array $countries = []): array
    {
        return ['statuses' => $all
            ? [MemberStatusType::ACTIVE, ...MemberStatusType::SIGNUP_UNFINISHED]
            : [MemberStatusType::ACTIVE]] + ([] === $countries ? [] : ['countries' => $countries]);
    }

    /**
     * @return array<string, ArrayParameterType>
     */
    private function types(): array
    {
        return ['statuses' => ArrayParameterType::STRING, 'countries' => ArrayParameterType::INTEGER];
    }

    private function summary(string $aboutMe): string
    {
        $words = preg_split('/\s+/u', trim(html_entity_decode(strip_tags($aboutMe), \ENT_QUOTES, 'UTF-8')), -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', \array_slice($words, 0, self::SUMMARY_WORDS)) . (\count($words) > self::SUMMARY_WORDS ? ' …' : '');
    }
}
