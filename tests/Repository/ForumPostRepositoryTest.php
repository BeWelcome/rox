<?php

namespace App\Tests\Repository;

use App\Entity\ForumPost;
use App\Repository\ForumPostRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
class ForumPostRepositoryTest extends KernelTestCase
{
    private ForumPostRepository $repository;
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $entityManager->getRepository(ForumPost::class);
        $this->connection = $entityManager->getConnection();
    }

    public function testSearchIndexDocumentsUseThePostsOwnTextAndLanguage(): void
    {
        $posts = $this->connection->fetchAllAssociativeIndexed(<<<'SQL'
                SELECT fp.id, fp.message, fp.ShortCode, fp.IdWriter, ft.id AS thread_id, ft.IdGroup
                FROM forum_post fp JOIN forum_thread ft ON fp.threadid = ft.id
            SQL);
        self::assertNotEmpty($posts, 'the test fixtures contain forum posts');

        $count = $this->repository->countSearchIndexDocuments();
        $documents = $this->repository->getSearchIndexDocuments(0, $count);

        self::assertSame(\count($posts), $count);
        self::assertCount($count, $documents);
        foreach ($documents as $document) {
            $post = $posts[$document['post_id']];
            self::assertSame($post['message'], $document['content']);
            self::assertSame($post['ShortCode'], $document['locale']);
            self::assertSame((int) $post['IdWriter'], $document['author']);
            self::assertSame((int) $post['thread_id'], $document['thread_id']);
            // threads without a group are indexed with group 0, which the forum search relies on
            self::assertSame((int) ($post['IdGroup'] ?? 0), $document['group']);
        }
    }

    public function testSearchIndexDocumentsArePagedWithoutGapsOrDuplicates(): void
    {
        $count = $this->repository->countSearchIndexDocuments();
        self::assertGreaterThan(1, $count, 'the test fixtures contain more than one forum post');

        $pageSize = intdiv($count, 2);
        $ids = [];
        for ($offset = 0; $offset < $count; $offset += $pageSize) {
            foreach ($this->repository->getSearchIndexDocuments($offset, $pageSize) as $document) {
                $ids[] = $document['post_id'];
            }
        }

        self::assertCount($count, $ids);
        self::assertSame($ids, array_values(array_unique($ids)));
    }
}
