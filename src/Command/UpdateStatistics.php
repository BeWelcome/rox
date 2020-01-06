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
use Zend\Validator\Date;

class UpdateStatistics extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'statistics:update';

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
            ->addArgument('startDate', InputArgument::OPTIONAL, 'Start date')
            ->addArgument('endDate', InputArgument::OPTIONAL, 'Last date (included)')
            ->setHelp('This updates the stats table for the given period. Will overwrite existing values.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Updating statistics',
            '===================',
            '',
        ]);

        $startDateGiven = $input->getArgument('startDate');
        if ($startDateGiven) {
            $startDate = DateTime::createFromFormat('Y-m-d', $startDateGiven);
        } else {
            $startDate = new DateTime();
            $startDate->modify('-1 day');
        }

        // Check first to avoid false positives on end date if none given
        $today = new DateTime();
        $today->setTime(0, 0, 0, 0);
        if ($startDate >= $today) {
            $output->writeln([
                'Start date must be in the past (or empty for yesterday).'
            ]);
            return 1;
        }

        $endDateGiven = $input->getArgument('endDate');
        if ($endDateGiven) {
            $endDate = DateTime::createFromFormat('Y-m-d', $endDateGiven);
        } else {
            $endDate = new DateTime();
        }

        if (!$startDate || !$endDate) {
            $output->writeln([
                'start or end date not correctly formatted (need YYYY-MM-DD)',
                ''
            ]);
            return 1;
        }

        if ($startDate > $endDate) {
            $output->writeln([
                'start date must be earlier than end date',
                ''
            ]);
            return 1;
        }

        $startDate->setTime(0,0,0,0);
        $endDate->setTime(0,0,0,0);

        if ($startDate == $endDate) {
            $endDate->modify('+1 day');
        }

        $interval = new DateInterval( "P1D" );
        $dates = new DatePeriod($startDate, $interval, $endDate);

        return $this->statisticsModel->updateStatistics($dates, $output);
    }
}
