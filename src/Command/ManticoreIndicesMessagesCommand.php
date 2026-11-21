<?php

namespace App\Command;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\MessageSearchIndex;
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

/**
 * Builds the message search index (message_rt) from scratch. New messages are added as they are written
 * (MessageSearchIndexListener), also while this runs: the documents are replaced, not inserted, so a message the
 * listener indexed first doesn't stop the build with a duplicate id. The deploy builds it on the live site.
 */
#[AsCommand(
    name: 'manticore:indices:messages',
    description: 'Creates the manticore index for the search in the inbox',
)]
class ManticoreIndicesMessagesCommand extends Command
{
    private const int CHUNK_SIZE = 2500;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $manticoreHost,
        private readonly int $manticorePort,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');

        $io = new SymfonyStyle($input, $output);
        $io->note('Creating manticore message real-time index.');

        $client = $this->createClient();
        $index = $client->table(MessageSearchIndex::INDEX);
        try {
            // Rebuild from scratch: an existing index would otherwise get every message a second time
            $existing = $client->sql("SHOW TABLES LIKE '" . MessageSearchIndex::INDEX . "'", true);
            if (!empty($existing)) {
                $index->drop();
                $io->note('Dropped existing ' . MessageSearchIndex::INDEX . ' index.');
            }

            $index->create(
                [
                    'subject_id' => ['type' => 'integer'],
                    'sender' => ['type' => 'integer'],
                    'receiver' => ['type' => 'integer'],
                    'content' => ['type' => 'text', 'options' => ['indexed']],
                ],
                [
                    'html_strip' => '1',
                    'min_prefix_len' => '2',
                    'charset_table' => 'non_cjk',
                    'ngram_chars' => 'cjk',
                    'ngram_len' => '1',
                ]
            );
        } catch (Exception $e) {
            $io->error('Could not create index ' . MessageSearchIndex::INDEX . ': ' . $e->getMessage());

            return Command::FAILURE;
        }

        // All messages go in without auto-optimize, then the disk chunks are merged once: otherwise Manticore may start
        // merging in the middle of the insert, as it did in the places index (ManticoreIndicesGeonamesCommand), and
        // close the connection. Auto-optimize is switched on again whatever happens.
        $this->rawSql($client, 'SET GLOBAL auto_optimize=0');
        try {
            $this->addDocuments($index, $output);
            $io->newLine();
            $io->note('Optimizing ' . MessageSearchIndex::INDEX . ' (merging its disk chunks).');
            $this->rawSql($client, 'OPTIMIZE TABLE ' . MessageSearchIndex::INDEX . ' OPTION cutoff=1, sync=1');
        } finally {
            $this->rawSql($client, 'SET GLOBAL auto_optimize=1');
        }

        $io->success('Created ' . MessageSearchIndex::INDEX . '.');

        return Command::SUCCESS;
    }

    protected function createClient(): Client
    {
        return new Client(['host' => $this->manticoreHost, 'port' => $this->manticorePort]);
    }

    /**
     * Raw: the /sql endpoint only takes these raw, and the client can't parse their empty answer.
     */
    private function rawSql(Client $client, string $statement): void
    {
        try {
            $client->sql($statement, true);
        } catch (Exception $exception) {
            if ('' !== $exception->getMessage()) {
                throw $exception;
            }
        }
    }

    private function addDocuments(Table $index, OutputInterface $output): void
    {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        $progressBar = new ProgressBar($output, $repository->countSearchIndexDocuments());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->minSecondsBetweenRedraws(5);
        $progressBar->start();

        $afterId = 0;
        while ([] !== $documents = $repository->getSearchIndexDocuments($afterId, self::CHUNK_SIZE)) {
            $index->replaceDocuments($documents);
            $afterId = end($documents)['id'];
            $progressBar->advance(\count($documents));

            unset($documents);
            gc_collect_cycles();
        }

        $progressBar->finish();
    }
}
