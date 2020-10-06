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

        // always successful
        return 0;
    }

    private function updateGeonamesForDate(DateTime $date, SymfonyStyle $io): int
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
                        `geonamesadminunits`
                    SET
                        geonameid = :geoname_id,
                        name = :name,
                        fclass = :fclass,
                        fcode = :fcode,
                        country = :country,
                        admin1 = :admin1,
                        moddate = :mod_date
                ');
                $statement->execute([
                    ':geoname_id' => $change[0],
                    ':name' => $connection->quote($change[1], ParameterType::STRING),
                    ':fclass' => $change[6],
                    ':fcode' => $change[7],
                    ':country' => $change[8],
                    ':admin1' => $change[10],
                    ':mod_date' => $change[18],
                ]);
                if ('A' === $change[6]) {
                    // update geonamesadminunits accordingly
                    $statement = $connection->prepare('
    				    REPLACE INTO
    				        `geonamesadminunits`
    				    SET
                            geonameid = :geoname_id,
                            name = :name,
                            fclass = :fclass,
                            fcode = :fcode,
                            country = :country,
                            admin1 = :admin1,
                            moddate = :mod_date
    				');
                    $statement->execute([
                        ':geoname_id' => $change[0],
                        ':name' => $connection->quote($change[1], ParameterType::STRING),
                        ':fclass' => $change[6],
                        ':fcode' => $change[7],
                        ':country' => $change[8],
                        ':admin1' => $change[10],
                        ':mod_date' => $change[18],
                    ]);
                }
            }
        }

        $deletes = $this->fetchFile('http://download.geonames.org/export/dump/' .
            'deletes-' . $date->format('Y-m-d') . '.txt');
        foreach($deletes as $delete) {
            $removeGeonameId = $delete[0];
            // handle duplication
            if (0 === strpos('duplicate ', $delete[2])) {
                $newGeonameId = str_replace('duplicate ', '', $delete[2]);
                $this->handleDuplicates($removeGeonameId, $newGeonameId);

            }
            // Remove id from data base
            $statement =  $connection->prepare("
                DELETE FROM
                    `geonames`
                WHERE
                    geonameid = :geoname_id
            ");
            $statement->execute([
                ':geoname_id' => $removeGeonameId,
            ]);
        }

        return $count;
    }

    private function updateAlternateNamesForDate($date): int
    {
        $count = 0;
        $connection = $this->entityManager->getConnection();

        $changes = $this->fetchFile('http://download.geonames.org/export/dump/'
            . 'alternateNamesModifications-' . $date->format('Y-m-d') . '.txt');
        foreach ($changes as $change) {
            if (is_numeric($change[0] && 'link' !== $change[2])) {
                // 0 alternatenameid, 1 geonameid, 2 isolanguage, 3 alternatename, 4 ispreferred, 5 isshort, 6 iscolloquial, 7 ishistoric
                $statement = $connection->prepare('
                    REPLACE INTO
                        `geonamesalternatenames`
                    SET
                        alternatenameId = :alternate_id,
                        geonameid = :geoname_id,
                        isolanguage = :isolanguage,
                        alternatename = :alternatename,
                        ispreferred = :ispreferred,
                        isshort = :isshort,
                        iscolloquial = :iscolloquial,
                        ishistoric = :ishistoric
                ');
                $statement->execute([
                    ':alternate_id' => $change[0],
                    ':geoname_id' => $change[1],
                    ':isolanguage' => $change[2],
                    ':alternatename' => $connection->quote($change[3], ParameterType::STRING),
                    ':ispreferred' => $change[4],
                    ':isshort' => $change[5],
                    ':iscolloquial' => $change[6],
                    ':ishistoric' => $change[7],
                ]);
            }
        }

        $deletes = $this->fetchFile('http://download.geonames.org/export/dump/'
            . 'alternateNamesDeletes-' . $date->format('Y-m-d') . '.txt');
        foreach ($deletes as $delete) {
            if (is_numeric($delete[0])) {
                $statement = $connection->prepare("
    				DELETE FROM
    				    `geonamesalternatenames`
    				WHERE
    				    alternatenameid = :alternate_id
    				    AND geonameid = :geoname_id
    		    ");
                $statement->execute([
                    ':alternate_id' => $delete[0],
                    ':geoname_id' => $delete[1],
                ]);
            }
        }

        return $count;
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
        $result = $this->updateAlternateNamesForDate((new DateTime())->modify('-1day'), $io); // Yesterday
        $result |= $this->updateAlternateNamesForDate((new DateTime())->modify('-2days'), $io); // the day before yesterday

        return $result;
    }

    private function handleDuplicates($removeGeonameId, $newGeonameId)
    {
        $connection = $this->entityManager->getConnection();

        // First update members table which points to the old geoname id
        $statement =  $connection->prepare("
                UPDATE
                    `members`
                SET 
                    IdCity = :new_geoname_id
                WHERE
                    IdCity = :old_geoname_id
            ");
        $statement->execute([
            ':old_geoname_id' => $removeGeonameId,
            ':new_geoname_id' => $newGeonameId,
        ]);

        // Second update addresses table
        $statement =  $connection->prepare("
                UPDATE
                    `addresses`
                SET 
                    IdCity = :new_geoname_id
                WHERE
                    IdCity = :old_geoname_id
            ");
        $statement->execute([
            ':old_geoname_id' => $removeGeonameId,
            ':new_geoname_id' => $newGeonameId,
        ]);

        // Third update activities table
        $statement =  $connection->prepare("
                UPDATE
                    `activities`
                SET 
                    locationId = :new_geoname_id
                WHERE
                    locationId = :old_geoname_id
            ");

        $statement->execute([
            ':old_geoname_id' => $removeGeonameId,
            ':new_geoname_id' => $newGeonameId,
        ]);

        $statement =  $connection->prepare("
                UPDATE
                    `geonames`
                SET 
                    geonameid = :new_geoname_id
                WHERE
                    geonameid = :old_geoname_id
            ");
        $statement->execute([
            ':old_geoname_id' => $removeGeonameId,
            ':new_geoname_id' => $newGeonameId,
        ]);
    }
}
