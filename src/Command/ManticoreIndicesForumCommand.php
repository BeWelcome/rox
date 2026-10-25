<?php

namespace App\Command;

use App\Entity\ForumPost;
use App\Repository\ForumPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Manticoresearch\Client;
use Manticoresearch\Table;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'manticore:indices:forum',
    description: 'Creates the manticore indices for the forum search',
    aliases: [],
    hidden: false,
)]
class ManticoreIndicesForumCommand extends Command
{
    private const string FORUM_INDEX = 'forum_rt';
    private int $chunkSize = 2500;
    private SymfonyStyle $io;

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly string $manticoreHost, private readonly int $manticorePort)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->note('Creating manticore forum real-time index.');
        $this->io->newLine();

        $index = $this->createForumIndex();
        if (null === $index) {
            return Command::FAILURE;
        }

        $this->addForumDocuments($index, $output);

        $this->io->newLine();
        $this->io->note('Created ' . self::FORUM_INDEX . '.');

        $this->io->success('Created Manticore index.');

        return Command::SUCCESS;
    }

    private function createForumIndex(): ?Table
    {
        $client = new Client(['host' => $this->manticoreHost, 'port' => $this->manticorePort]);
        $index = $client->table(self::FORUM_INDEX);

        try {
            // Rebuild from scratch: an existing index would otherwise get every post a second time
            $existing = $client->sql("SHOW TABLES LIKE '" . self::FORUM_INDEX . "'", true);
            if (!empty($existing)) {
                $index->drop();
                $this->io->note('Dropped existing ' . self::FORUM_INDEX . ' index.');
            }

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
            $this->io->error('Could not create index ' . self::FORUM_INDEX . ': ' . $e->getMessage());

            return null;
        }

        return $index;
    }

    private function addForumDocuments(Table $index, OutputInterface $output): void
    {
        $this->io->note('Adding documents to ' . self::FORUM_INDEX . ' from forum and group tables.');
        $this->io->newLine();

        /** @var ForumPostRepository $repository */
        $repository = $this->entityManager->getRepository(ForumPost::class);
        $count = $repository->countSearchIndexDocuments();
        if (0 === $count) {
            return;
        }

        $progressBar = $this->getProgressBar($output, $count);
        for ($offset = 0; $offset < $count; $offset += $this->chunkSize) {
            $documents = $repository->getSearchIndexDocuments($offset, $this->chunkSize);
            if (empty($documents)) {
                break;
            }
            $index->addDocuments($documents);
            $index->flush();
            $progressBar->advance(\count($documents));

            unset($documents);
            gc_collect_cycles();
        }

        $progressBar->finish();
        $this->io->newLine();
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
