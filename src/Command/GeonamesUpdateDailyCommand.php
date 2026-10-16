<?php

namespace App\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 *
 * \todo Command currently not used. Update to allow to use it.
 */
#[AsCommand(
    name: 'geonames:update:daily',
    description: 'Update the geonames data with the latest additions (no deletions!).',
    aliases: [],
    hidden: false,
)] class GeonamesUpdateDailyCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
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

    private function fetchFile($url): array
    {
        $content = [];
        $handle = fopen($url, 'r');
        if (!$handle) {
            return $content;
        }
        while (false !== ($data = fgetcsv($handle, 0, "\t", escape: '\\'))) {
            $content[] = $data;
        }

        return $content;
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
                $statement->execute();
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
                    $statement->execute();
                }
            }
        }

        $deletes = $this->fetchFile('http://download.geonames.org/export/dump/' .
            'deletes-' . $date->format('Y-m-d') . '.txt');
        foreach ($deletes as $delete) {
            $removeGeonameId = $delete[0];
            // handle duplication
            if (str_starts_with('duplicate ', (string) $delete[2])) {
                $newGeonameId = str_replace('duplicate ', '', $delete[2]);
                $this->handleDuplicates($removeGeonameId, $newGeonameId);
            }
            // Remove id from data base
            $statement = $connection->prepare('
                DELETE FROM
                    `geonames`
                WHERE
                    geonameid = :geoname_id
            ');
            $statement->execute();
        }

        return $count;
    }

    private function updateAlternateNamesForDate($date, SymfonyStyle $io): int
    {
        $io->note('Alternate names: Working on date ' . $date->format('Y-m-d'));

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
                $statement->execute();
            }
        }

        $deletes = $this->fetchFile('http://download.geonames.org/export/dump/'
            . 'alternateNamesDeletes-' . $date->format('Y-m-d') . '.txt');
        foreach ($deletes as $delete) {
            if (is_numeric($delete[0])) {
                $statement = $connection->prepare('
    				DELETE FROM
    				    `geonamesalternatenames`
    				WHERE
    				    alternatenameid = :alternate_id
    				    AND geonameid = :geoname_id
    		    ');
                $statement->execute();
            }
        }

        return $count;
    }

    /**
     * get updates from geonames.
     **/
    private function updateGeonames($io): void
    {
        $this->updateGeonamesForDate((new DateTime())->modify('-1day'), $io); // Yesterday
        $this->updateGeonamesForDate((new DateTime())->modify('-2days'), $io); // the day before yesterday
        if ('01' === date('d', time())) {
            // \todo: Update country list on the first day of a month
        }
    }

    private function updateAlternatenames($io): int
    {
        $result = $this->updateAlternateNamesForDate((new DateTime())->modify('-1day'), $io); // Yesterday
        $result |= $this->updateAlternateNamesForDate((new DateTime())->modify('-2days'), $io); // the day before yesterday

        return $result;
    }

    private function handleDuplicates($removeGeonameId, $newGeonameId): void
    {
        $connection = $this->entityManager->getConnection();

        // First update members table which points to the old geoname id
        $statement = $connection->prepare('
                UPDATE
                    `members`
                SET
                    IdCity = :new_geoname_id
                WHERE
                    IdCity = :old_geoname_id
            ');
        $statement->execute();

        // Second update addresses table
        $statement = $connection->prepare('
                UPDATE
                    `addresses`
                SET
                    IdCity = :new_geoname_id
                WHERE
                    IdCity = :old_geoname_id
            ');
        $statement->execute();

        // Third update activities table
        $statement = $connection->prepare('
                UPDATE
                    `activities`
                SET
                    locationId = :new_geoname_id
                WHERE
                    locationId = :old_geoname_id
            ');

        $statement->execute();

        $statement = $connection->prepare('
                UPDATE
                    `geonames`
                SET
                    geonameid = :new_geoname_id
                WHERE
                    geonameid = :old_geoname_id
            ');
        $statement->execute();
    }
}
