<?php

namespace App\Service;

use App\Entity\ForumPost;
use Manticoresearch\Client;
use Manticoresearch\Search;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * Keeps a post's document in the forum search index (forum_rt, see ManticoreIndicesForumCommand) up to date. The
 * forum works without the search: failures are logged, not thrown.
 */
class ForumSearchIndex
{
    private const string INDEX = 'forum_rt';

    public function __construct(
        #[Autowire('%manticore.host%')] private readonly string $host,
        #[Autowire('%manticore.port%')] private readonly int $port,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Adds the post, or replaces its document: its own text and language (as the index command does) and its
     * attributes.
     */
    public function index(ForumPost $post): void
    {
        $thread = $post->getThread();
        if (null === $thread) {
            return;
        }

        $document = [
            'post_id' => (int) $post->getId(),
            'post_deleted' => (string) $post->getDeleted(),
            'post_visibility' => (string) $post->getPostvisibility(),
            'thread_id' => (int) $thread->getId(),
            'thread_deleted' => (string) $thread->getDeleted(),
            'thread_visibility' => (string) $thread->getVisibility(),
            'content' => (string) $post->getMessage(),
            'group' => (int) $thread->getGroup()?->getId(),
            'author' => (int) $post->getAuthor()->getId(),
            'locale' => $post->getLanguage()->getShortCode(),
        ];

        try {
            $client = new Client(['host' => $this->host, 'port' => $this->port]);
            $search = new Search($client);
            $search->setTable(self::INDEX)->filter('post_id', 'equals', $document['post_id']);
            $results = $search->get();
            $results->rewind();
            $table = $client->table(self::INDEX);
            if ($results->valid()) {
                $table->replaceDocument($document, $results->current()->getId());
            } else {
                $table->addDocument($document);
            }
        } catch (Throwable $throwable) {
            $this->logger->warning('Forum post #{post} could not be indexed: {message}', [
                'post' => $document['post_id'],
                'message' => $throwable->getMessage(),
            ]);
        }
    }
}
