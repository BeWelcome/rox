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
 * (MessageSearchIndexListener).
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

        $client = new Client(['host' => $this->manticoreHost, 'port' => $this->manticorePort]);
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

        $this->addDocuments($index, $output);

        $io->newLine();
        $io->success('Created ' . MessageSearchIndex::INDEX . '.');

        return Command::SUCCESS;
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
            $index->addDocuments($documents);
            $afterId = end($documents)['id'];
            $progressBar->advance(\count($documents));

            unset($documents);
            gc_collect_cycles();
        }

        $progressBar->finish();
    }
}
