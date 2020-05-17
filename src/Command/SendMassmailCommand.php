<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendMassmailCommand extends Command
{
    protected static $defaultName = 'send:massmail';

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
            $batchSize = 100;
        }

        $io->success(sprintf('Send %d messages', $batchSize));

        return 0;
    }
}
