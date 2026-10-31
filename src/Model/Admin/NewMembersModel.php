<?php

namespace App\Model\Admin;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
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

    private const int DAYS = 90;
    private const int SUMMARY_WORDS = 70;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * With $all (the safety team and checkers) the unfinished signups and the members greeted twice as well,
     * oldest first; else the newest first.
     */
    public function getPager(bool $all, string $locale, int $page, int $perPage): Pagerfanta
    {
        $pager = new Pagerfanta(new CallbackAdapter(
            fn (): int => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM member m WHERE ' . $this->where($all), $this->parameters($all), $this->types()),
            fn (int $offset, int $length): array => $this->members($all, $locale, $offset, $length),
        ));
        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage(max(1, min($page, $pager->getNbPages())));

        return $pager;
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
    private function members(bool $all, string $locale, int $offset, int $length): array
    {
        // place and country in the viewer's language, else in English, else as GeoNames names them
        $placeName = static fn (string $alias): string => "COALESCE(
            (SELECT t.content FROM location_translation t WHERE t.object_id = {$alias}.geoname_id AND t.field = 'name'
                AND t.locale IN (:locale, 'en') ORDER BY t.locale = :locale DESC LIMIT 1),
            {$alias}.name)";

        $rows = $this->connection->fetchAllAssociative(
            'SELECT m.id, m.Username AS username, m.created, m.Status AS status, m.Email AS email,
                m.bewelcomed, m.AboutMe AS about_me, ' . $placeName('g') . ' AS place, ' . $placeName('c') . ' AS country
            FROM member m
            LEFT JOIN address a ON a.member_id = m.id AND a.active = 1
            LEFT JOIN location g ON g.geoname_id = a.location
            LEFT JOIN location c ON c.geoname_id = g.country
            WHERE ' . $this->where($all) . '
            ORDER BY m.created ' . ($all ? 'ASC' : 'DESC') . '
            LIMIT ' . $length . ' OFFSET ' . $offset,
            $this->parameters($all) + ['locale' => $locale],
            $this->types(),
        );

        $languages = $this->languages(array_map(static fn (array $row): int => (int) $row['id'], $rows));

        return array_map(fn (array $row): array => $row + [
            'summary' => $this->summary((string) $row['about_me']),
            'local_greeting_sent' => 0 !== ((int) $row['bewelcomed'] & self::LOCAL_GREETING),
            'global_greeting_sent' => 0 !== ((int) $row['bewelcomed'] & self::GLOBAL_GREETING),
            'languages' => $languages[(int) $row['id']] ?? [],
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

    private function where(bool $all): string
    {
        return 'm.Status IN (:statuses) AND m.created > NOW() - INTERVAL ' . self::DAYS . ' DAY'
            . ($all ? '' : ' AND m.bewelcomed < ' . (self::LOCAL_GREETING | self::GLOBAL_GREETING));
    }

    /**
     * @return array<string, mixed>
     */
    private function parameters(bool $all): array
    {
        return ['statuses' => $all
            ? [MemberStatusType::ACTIVE, ...MemberStatusType::SIGNUP_UNFINISHED]
            : [MemberStatusType::ACTIVE]];
    }

    /**
     * @return array<string, ArrayParameterType>
     */
    private function types(): array
    {
        return ['statuses' => ArrayParameterType::STRING];
    }

    private function summary(string $aboutMe): string
    {
        $words = preg_split('/\s+/u', trim(html_entity_decode(strip_tags($aboutMe), \ENT_QUOTES, 'UTF-8')), -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', \array_slice($words, 0, self::SUMMARY_WORDS)) . (\count($words) > self::SUMMARY_WORDS ? ' …' : '');
    }
}
