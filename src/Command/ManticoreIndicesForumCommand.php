<?php

namespace App\Command;

use App\Entity\NewLocation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Manticoresearch\Client;
use Manticoresearch\Index;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;

class ManticoreIndicesForumCommand extends Command
{
    private const FORUM_INDEX = 'forum_rt';
    private int $chunkSize = 2500;

    protected static $defaultName = 'manticore:indices:forum';
    protected static $defaultDescription = 'Creates the manticore indices for the forum search';
    private EntityManagerInterface $entityManager;
    private SymfonyStyle $io;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct(self::$defaultName);

        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->note('Creating manticore forum real-time index.');
        $this->io->newLine();

        $index = $this->createForumIndex();
        if (null !== $index) {
            $this->addForumDocuments($index, $output);

            $this->io->newLine();
            $this->io->note('Created ' . self::FORUM_INDEX . '.');
        } else {
            $this->io->note(
                'Skipped creation of ' . self::FORUM_INDEX . ' index. ' . PHP_EOL .
                'Index already exists.'
            );
            return Command::INVALID;
        }

        $this->io->success('Created Manticore index.');

        return Command::SUCCESS;
    }

    private function createForumIndex(): ?Index
    {
        $client = new Client(['host' => '127.0.0.1','port' => 9412]);
        $index = $client->index('forum_rt');

        try {
            $index->create(
                [
                    'post_id' => ['type' => 'integer'],
                    'post_deleted' => ['type' => 'string'],
                    'post_visibility' => ['type' => 'string'],
                    'thread_id' => ['type' => 'integer'],
                    'thread_deleted' => ['type' => 'string'],
                    'thread_visibility' => ['type' => 'string'],
                    'content' => ['type' => 'text'],
                    'group' => ['type' => 'integer'],
                    'author' => ['type' => 'integer'],
                    'locale' => ['type' => 'string'],
                ],
                [
                    'index_exact_words' => '1',
                    'expand_keywords' => '0', // don't support wildcards upfront only in searches
                    'charset_table' => 'non_cjk,U+0020->_', // Ignore spaces (turns e.g. Los Angeles into Los_Angeles)
                    'min_prefix_len' => '1',
                    'prefix_fields' => 'name',
                    'ngram_chars' => 'cjk',
                    'ngram_len' => '1',
                ]
            );
        } catch (Exception $e) {
            // $index = null;

            $this->io->error($e->getMessage());
            $this->io->error('Index ' . self::FORUM_INDEX . ' already exists or another problem occurred.');
        }

        return $index;
    }

    private function addForumDocuments(Index $index, OutputInterface $output)
    {
        /*
          SELECT ft.id AS threadid, sentence as text, IdGroup, \
            IdWriter, UNIX_TIMESTAMP(create_time) as created FROM forums_threads ft, forums_posts fp, forum_trads ftr \
        WHERE fp.id = ft.first_postid AND ft.IdTitle = ftr.IdTrad AND ftr.IdLanguage = 0 AND ThreadDeleted = 'NotDeleted'

        sql_joined_field = text from query; SELECT fp.id, message FROM forums_posts fp WHERE PostDeleted = 'NotDeleted' ORDER BY fp.id ASC
    sql_joined_field = text from query; SELECT ft.id, Sentence FROM forums_threads ft, forum_trads ftr WHERE ft.ThreadDeleted = "NotDeleted" AND ft.IdTitle = ftr.IdTrad AND IDLanguage <> 0 ORDER BY ft.id ASC


         */
        $this->io->note('Adding documents to ' . self::FORUM_INDEX . ' from forum and group tables.');
        $this->io->newLine();

        $stmt = $this->entityManager
            ->getConnection()
            ->executeQuery(<<<___SQL
            SELECT
                count(*) as cnt
            FROM
                forums_posts fp
        ___SQL);

        $count = ($stmt->fetchNumeric())[0];

        $progressBar = $this->getProgressBar($output, $count);

        $firstResult = 0;
        do {
            $query = $this->entityManager->createNativeQuery(<<<___SQL
                SELECT
                    fp.id as post_id,
                    fp.PostDeleted as post_deleted,
                    fp.PostVisibility as post_visibility,
                    ft.id AS thread_id,
                    ft.ThreadDeleted AS thread_deleted,
                    ft.ThreadVisibility AS thread_visibility,
                    ftr.Sentence as content,
                    ft.IdGroup AS `group`,
                    fp.IdWriter as author,
                    l.shortcode as locale
                FROM
                    forums_posts fp
                JOIN forums_threads ft ON fp.threadid = ft.id
                JOIN forum_trads ftr ON fp.IdContent = ftr.IdTrad
                JOIN languages l ON ftr.IdLanguage = l.id
                LIMIT {$firstResult}, {$this->chunkSize}
            ___SQL
                , $this->getResultSetMappingForForumIndex());

            $addDocumentsCount = $this->addForumDocumentsToIndex($index, $query, $progressBar);

            $firstResult += $this->chunkSize;
        } while ($addDocumentsCount > 0);

        $progressBar->finish();
        $this->io->newLine();
    }

    private function addForumDocumentsToIndex(Index $index, NativeQuery $query, ProgressBar $progress): int
    {
        $forumPosts = $query->getResult();
        $documents = [];

        foreach ($forumPosts as $forumPost) {
            $documents[] = [
                'post_id' => $forumPost['post_id'],
                'post_deleted' =>  $forumPost['post_deleted'],
                'post_visibility' =>  $forumPost['post_visibility'],
                'thread_id' =>  $forumPost['thread_id'],
                'thread_deleted' =>  $forumPost['thread_deleted'],
                'thread_visibility' =>  $forumPost['thread_visibility'],
                'content' =>  $forumPost['content'],
                'group' =>  $forumPost['group'] ?? 0,
                'author' =>  $forumPost['author'],
                'locale' =>  $forumPost['locale'],
            ];
            $progress->advance();
        }
        $count = \count($forumPosts);
        unset($forumPosts);

        $index->addDocuments($documents);
        $index->flush();

        gc_collect_cycles();

        return $count;
    }

    private function getResultSetMappingForForumIndex(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('post_id', 'post_id')
            ->addScalarResult('post_deleted', 'post_deleted')
            ->addScalarResult('post_visibility', 'post_visibility')
            ->addScalarResult('thread_id', 'thread_id')
            ->addScalarResult('thread_deleted', 'thread_deleted')
            ->addScalarResult('thread_visibility', 'thread_visibility')
            ->addScalarResult('content', 'content')
            ->addScalarResult('group', 'group')
            ->addScalarResult('author', 'author')
            ->addScalarResult('locale', 'locale')
            ->addScalarResult('created', 'created')
        ;
        return $rsm;
    }
    private function getProgressBar(OutputInterface $output, $count): ProgressBar
    {
        $progressBar = new ProgressBar($output, $count);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->start();
        $progressBar->setRedrawFrequency(1000);
        $progressBar->minSecondsBetweenRedraws(5);

        return $progressBar;
    }
}
