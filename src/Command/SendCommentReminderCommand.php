<?php

namespace App\Command;

use App\Service\Mailer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SendCommentReminderCommand extends Command
{
    protected static $defaultName = 'comments:send:reminder';

    private EntityManagerInterface $entityManager;
    /**
     * @var Mailer
     */
    private Mailer $mailer;
    private int $batchSize;

    public function __construct(
        EntityManagerInterface $entityManager,
        Mailer $mailer,
        int $batchSize
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->batchSize = $batchSize;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a batch of massmail email everytime the command is called')
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'Count of mails send while the command is running')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = $input->getArgument('batchSize');

        if (!$batchSize) {
            // use default from service configuration if not given as option on command call
            $batchSize = $this->batchSize;
        }

        return Command::SUCCESS;
    }
}
