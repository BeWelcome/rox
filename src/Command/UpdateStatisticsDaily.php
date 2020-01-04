<?php

namespace App\Command;

use App\Model\StatisticsModel;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStatisticsDaily extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'statistics:update:daily';

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
            ->setHelp('This updates the stats table with a row with today\'s current numbers. Will overwrite existing values.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Updating statistics for yesterday',
            '=================================',
            '',
        ]);

        $start = new DateTime();
        $start = $start->modify('-1 day');
        $start->setTime(0,0,0, 0);
        $end = new DateTime();
        $end->setTime(0,0,0, 0);
        $interval = new DateInterval( "P1D" );
        $dates = new DatePeriod($start, $interval, $end);

        $output->writeln([
            'Finished',
        ]);
        return $this->statisticsModel->updateStatistics($dates, $output);
    }
}
