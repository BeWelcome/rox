<?php

namespace App\Service;

use App\Doctrine\ForumDeleteStatusType;
use Manticoresearch\Client;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\MatchPhrase;
use Manticoresearch\Search;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * Finds forum posts in the search index (forum_rt, see ManticoreIndicesForumCommand). Who may read a hit is up to
 * the caller (ForumVoter); this only leaves out deleted posts and threads unless asked for them.
 */
class ForumSearch
{
    private const string INDEX = 'forum_rt';
    private const int MAX_HITS = 1000;

    public function __construct(
        #[Autowire('%manticore.host%')] private readonly string $host,
        #[Autowire('%manticore.port%')] private readonly int $port,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return list<int> the ids of the posts that contain the phrase, newest first; empty if the search isn't there
     */
    public function findPostIds(string $phrase, bool $withDeleted, ?int $groupId = null): array
    {
        $phrase = trim($phrase);
        if ('' === $phrase) {
            return [];
        }

        $query = new BoolQuery();
        $query->must(new MatchPhrase($phrase, 'content'));
        if (!$withDeleted) {
            $query
                ->mustNot(new Equals('post_deleted', ForumDeleteStatusType::DELETED))
                ->mustNot(new Equals('thread_deleted', ForumDeleteStatusType::DELETED));
        }
        if (null !== $groupId) {
            $query->must(new Equals('group', $groupId));
        }

        try {
            $search = new Search(new Client(['host' => $this->host, 'port' => $this->port]));
            $results = $search
                ->setTable(self::INDEX)
                ->search($query)
                ->sort(['post_id' => 'DESC'])
                ->limit(self::MAX_HITS)
                ->get();
        } catch (Throwable $throwable) {
            $this->logger->warning('The forum search failed: {message}', ['message' => $throwable->getMessage()]);

            return [];
        }

        $postIds = [];
        foreach ($results as $hit) {
            $postIds[(int) $hit->getData()['post_id']] = true;
        }

        return array_keys($postIds);
    }
}
