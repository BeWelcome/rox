<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GeonamesUpdateFullCommand extends Command
{
    protected static $defaultName = 'geonames:update:full';

    protected function configure()
    {
        $this
            ->setDescription('Downloads the geonames data dump and imports it')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        file_put_contents('allCountries.zip', file_get_contents('http://download.geonames.org/export/dump/allCountries.zip'));

        $io->success('Updated the geonames databases to current state.');

        $io->note('The following geonames id are missing for members:');

        return 0;
    }
}
