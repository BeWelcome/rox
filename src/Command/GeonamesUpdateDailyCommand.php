<?php

namespace App\Command;

use DateTime;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GeonamesUpdateDailyCommand extends Command
{
    protected static $defaultName = 'geonames:update:daily';

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->params = $params;
        $this->entityManager = $entityManager;
    }

    public function fetchFile($url)
    {
        $content = [];
        $handle = @fopen($url, 'r');
        if (!$handle) {
            return $content;
        }
        while (false !== ($data = fgetcsv($handle, 0, "\t"))) {
            $content[] = $data;
        }

        return $content;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update the geonames data with the latest additions (no deletions!).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->updateGeonames($io);
        $this->updateAlternatenames($io);
        $io->success('Geonames data update successful.');
    }

    private function updateGeonamesForDate(DateTime $date, SymfonyStyle $io): void
    {
        $io->note('Working on date ' . $date->format('Y-m-d'));

        $count = 0;
        $connection = $this->entityManager->getConnection();

        $changes = $this->fetchFile('http://download.geonames.org/export/dump/modifications-' . $date->format('Y-m-d') . '.txt');
        foreach ($changes as $change) {
            if (is_numeric($change[0]) && ('A' === $change[6] || 'P' === $change[6])) {
                ++$count;
                // (0 geonameid, 1 name, 2 @skip, 3 @skip, 4 latitude, 5 longitude, 6 fclass, 7 fcode, 8 country, 9 @skip, 10 admin1,
                // 11 @skip, 12 @skip, 13 @skip, 14 population, 15 @skip, 16 @skip, 17 @skip, 18 moddate);
                $statement = $connection->prepare('
				    REPLACE INTO
				        `geonames`
				    SET
				        geonameid = :geonameId,
				        name = :name
				        ');
                $statement->execute([
                    ':geonameId' => $change[0],
                    ':name' => $connection->quote($change[1], ParameterType::STRING),
                ]);
                if ('A' === $change[6]) {
                    // update geonamesadminunits accordingly
                    $statement = $connection->prepare('
    				    REPLACE INTO
    				        `geonamesadminunits`
    				    SET
                            geonameid = :geonameId,
                            name = :name,
                            fclass = :flcass,
                            fcode = :fcode,
                            country = :country,
                            admin1 = :admin1,
                            moddate = :moddate
    				        ');
                    $statement->execute([
                        ':geonameId' => $change[0],
                        ':name' => $connection->quote($change[1], ParameterType::STRING),
                        ':fclass' => $change[6],
                        ':fcode' => $change[7],
                        ':country' => $change[9],
                        ':admin1' => $change[10],
                        ':moddate' => $change[18],
                    ]);
                    $statement->fetchAll();
                }
            }
        }

        /*        $deletes = $this->fetchFile('http://download.geonames.org/export/dump/deletes-'.$date.'.txt');
                foreach($deletes as $delete) {
                    if (is_numeric($delete[0])) {
                        $res = $this->dao->query("
                            DELETE FROM
                                `geonames`
                            WHERE
                                geonameid = '" . $this->dao->escape($delete[0]) . "'");
                        if (!$res) {
                            $result = false;
                        }
                    }
                }
        */
    }

    private function updateAltnames($date)
    {
        $result = true;
        $changes = $this->fetchFile('http://download.geonames.org/export/dump/alternateNamesModifications-' . $date . '.txt');
        foreach ($changes as $change) {
            if (is_numeric($change[0])) {
                $query = "
				    REPLACE INTO
				        `geonamesalternatenames`
				    SET
				        alternateNameId = '" . $this->dao->escape($change[0]) . "',
				        geonameid = '" . $this->dao->escape($change[1]) . "',
				        isolanguage = '" . $this->dao->escape($change[2]) . "',
				        alternateName = '" . $this->dao->escape($change[3]) . "',
				        ispreferred = '" . $this->dao->escape($change[4]) . "',
				        isshort = '" . $this->dao->escape($change[5]) . "',
				        isColloquial = '" . $this->dao->escape($change[6]) . "',
				        isHistoric = '" . $this->dao->escape($change[7]) . "'";
                $res = $this->dao->query($query);
                if (!$res) {
                    $result = false;
                }
            }
        }

        $deletes = $this->fetchFile('http://download.geonames.org/export/dump/alternateNamesDeletes-' . $date . '.txt');
        foreach ($deletes as $delete) {
            if (is_numeric($delete[0])) {
                $res = $this->dao->query("
    				DELETE FROM
    				    `geonamesalternatenames`
    				WHERE
    				    alternatenameid = '" . $this->dao->escape($delete[0]) . "'
    				    AND geonameid = '" . $this->dao->escape($delete[1]) . "'");
                if (!$res) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * get updates from geonames.
     *
     * @param mixed $io
     **/
    private function updateGeonames($io)
    {
        $this->updateGeonamesForDate((new DateTime())->modify('-1day'), $io); // Yesterday
        $this->updateGeonamesForDate((new DateTime())->modify('-2days'), $io); // the day before yesterday
        if ('01' === date('d', time())) {
            // \todo: Update country list on the first day of a month
        }
    }

    private function updateAlternatenames($io)
    {
        $result = $this->updateAltnames((new DateTime())->modify('-1day'), $io); // Yesterday
        $result |= $this->updateAltnames((new DateTime())->modify('-2days'), $io); // the day before yesterday

        return $result;
    }
}
