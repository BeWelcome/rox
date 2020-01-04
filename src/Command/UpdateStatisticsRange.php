<?php

namespace App\Command;

use App\Model\StatisticsModel;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStatisticsRange extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'statistics:update:dates';

    /** @var StatisticsModel */
    private $statisticsModel;

    public function __construct(StatisticsModel $statisticsModel)
    {
        $this->statisticsModel = $statisticsModel;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates the statistics for today')
            ->addArgument('startDate', InputArgument::REQUIRED, 'Start date')
            ->addArgument('endDate', InputArgument::REQUIRED, 'Last date (included)')
            ->setHelp('This updates the stats table for the fiven period. Will overwrite existing values.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Updating statistics for dates',
            '=============================',
            '',
        ]);

        $startDate = DateTime::createFromFormat('Y-m-d', $input->getArgument('startDate'));
        $endDate = DateTime::createFromFormat('Y-m-d', $input->getArgument('endDate'));

        if (!$startDate || !$endDate) {
            $output->writeln([
                'start or end date not correctly formatted (need YYYY-MM-DD)',
                ''
            ]);
            return -1;
        }

        if ($startDate > $endDate) {
            $output->writeln([
                'start date must be earlier than end date',
                ''
            ]);
            return -1;
        }

        $startDate->setTime(0,0,0,0);
        $endDate->setTime(0,0,0,0);

        $interval = new DateInterval( "P1D" );
        $dates = new DatePeriod($startDate, $interval, $endDate);

        return $this->statisticsModel->updateStatistics($dates, $output);
    }
}
