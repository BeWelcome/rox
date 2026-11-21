<?php

namespace App\Service;

use App\Entity\Member;
use Manticoresearch\Client;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\QueryString;
use Manticoresearch\Search;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * Finds the member's messages that contain some words, in the message search index (message_rt, see
 * ManticoreIndicesMessagesCommand). Which of them are still in a folder (deleted, spam, …) is up to the lists.
 */
class MessageSearch
{
    private const int MAX_HITS = 2000;

    public function __construct(
        #[Autowire('%manticore.host%')] private readonly string $host,
        #[Autowire('%manticore.port%')] private readonly int $port,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Every word must be there, as a word or the start of one ("berl" finds "Berlin").
     *
     * Returns the conversations with a match, the matching messages that have no conversation, and for each one its
     * newest matching message and how many match ('s' and the subject id, or 'm' and the message id for a message
     * without one).
     *
     * @return array{subjectIds: list<int>, messageIds: list<int>, hits: array<string, array{id: int, count: int}>}
     */
    public function find(Member $member, string $words): array
    {
        $found = ['subjectIds' => [], 'messageIds' => [], 'hits' => []];
        $queryString = self::queryString($words);
        if ('' === $queryString) {
            return $found;
        }

        $memberId = $member->getId();
        $ofMember = new BoolQuery();
        $ofMember
            ->should(new Equals('sender', $memberId))
            ->should(new Equals('receiver', $memberId));
        $query = new BoolQuery();
        $query
            ->must(new QueryString($queryString))
            ->must($ofMember);

        try {
            $search = new Search(new Client(['host' => $this->host, 'port' => $this->port]));
            $results = $search
                ->setTable(MessageSearchIndex::INDEX)
                ->search($query)
                ->sort('id', 'desc')
                ->limit(self::MAX_HITS)
                ->maxMatches(self::MAX_HITS)
                ->get();
        } catch (Throwable $throwable) {
            $this->logger->warning('The message search failed: {message}', ['message' => $throwable->getMessage()]);

            return $found;
        }

        $subjectIds = [];
        $messageIds = [];
        $hits = [];
        foreach ($results as $hit) {
            $messageId = (int) $hit->getId();
            $subjectId = (int) ($hit->getData()['subject_id'] ?? 0);
            if (0 === $subjectId) {
                $messageIds[$messageId] = true;
                $hits['m' . $messageId] = ['id' => $messageId, 'count' => 1];
            } else {
                $subjectIds[$subjectId] = true;
                // Newest first: the first hit of a conversation is its newest matching message
                $hits['s' . $subjectId] ??= ['id' => $messageId, 'count' => 0];
                ++$hits['s' . $subjectId]['count'];
            }
        }

        return ['subjectIds' => array_keys($subjectIds), 'messageIds' => array_keys($messageIds), 'hits' => $hits];
    }

    /**
     * The part of a message around the first of the words (at the start of a word, case aside), as plain text with
     * "…" where it was cut; null when the text doesn't have them (they were in the subject).
     */
    public static function snippet(string $html, string $words, int $before = 40, int $after = 90): ?string
    {
        $terms = array_map(
            static fn (string $term): string => preg_quote(rtrim($term, '*'), '/'),
            array_filter(explode(' ', self::queryString($words)))
        );
        if ([] === $terms) {
            return null;
        }

        $text = preg_replace('/<(br|\/p|\/div|\/li)[^>]*>/i', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        usort($terms, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));
        if (1 !== preg_match('/(?<![\p{L}\p{N}])(?:' . implode('|', $terms) . ')/iu', $text, $match, \PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $at = mb_strlen(substr($text, 0, $match[0][1]));
        $length = mb_strlen($text);
        $start = max(0, $at - $before);
        $end = min($length, $at + $after);
        // Cut between words
        if ($start > 0) {
            $space = mb_strpos($text, ' ', $start);
            $start = false !== $space && $space < $at ? $space + 1 : $start;
        }
        if ($end < $length) {
            $space = mb_strrpos(mb_substr($text, 0, $end), ' ');
            $end = false !== $space && $space > $at ? $space : $end;
        }

        return ($start > 0 ? '…' : '') . trim(mb_substr($text, $start, $end - $start)) . ($end < $length ? '…' : '');
    }

    /**
     * The words as a Manticore full-text query: each one escaped and as a prefix, all of them required.
     */
    public static function queryString(string $words): string
    {
        // Signs that mean something to Manticore split words, as they do in the index
        $words = preg_replace('/[\\\\()|\-!@~"&\/^$=<>*?%\'.,;:+\[\]{}]+/u', ' ', $words) ?? '';
        $terms = [];
        foreach (preg_split('/\s+/u', trim($words), -1, \PREG_SPLIT_NO_EMPTY) as $word) {
            $terms[] = $word . '*';
        }

        return implode(' ', $terms);
    }
}
