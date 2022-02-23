<?php

namespace App\Model;

use App\Entity\AdminUnit;
use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\MatchBuilder;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuggestLocationModel
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private SphinxQL $sphinxQL;
    private Connection $connection;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->connection = new Connection();
        $this->connection->setParams(array('host' => 'localhost', 'port' => 9306));

        $this->sphinxQL = new SphinxQL($this->connection);
    }

    /**
     * Search term looks like this:
     *
     * place[[, admin unit], admin unit|country]
     */
    public function getSuggestionsForPlaces(string $term, string $ranker): array
    {
        $placeAdminOrCountry = explode(',', $term);
        if (count($placeAdminOrCountry) > 3) {
            return ['locations' => []];
        }

        $adminId = null;
        $countryId = null;
        $place = trim($placeAdminOrCountry[0]);
        if (count($placeAdminOrCountry) == 2) {
            $countryOrAdmin = trim($placeAdminOrCountry[1]);
            list($countryId, $adminId) = $this->findCountryOrAdminId($countryOrAdmin);
        }

        if (count($placeAdminOrCountry) == 3) {
            $admin = trim($placeAdminOrCountry[1]);
            $country = trim($placeAdminOrCountry[2]);
            $countryId = $this->findCountryId($country);
            $adminId = $this->findAdminUnitId($countryId, $admin);
        }

        if (count($placeAdminOrCountry) !== 1 && null === $adminId && null === $countryId) {
            return ['locations' => []];
        }

        $match = (new MatchBuilder($this->sphinxQL))
            ->field('name')
            ->exact(SphinxQL::expr($place));

        $query = $this->sphinxQL->select('id', 'admin1', 'country')
            ->from('geonames')
            ->match($match)
            ->where('isPlace', '=', 1)
            ->option('ranker', SphinxQL::expr('expr(\'' . $ranker . '\')'))
            ->option('max_matches', 25)
        ;

        if (null !== $countryId) {
            $query->where('country', '=', $countryId);
        }

        if (null !== $adminId) {
            $query->where('admin1', '=', $adminId);
        }

        $compiled = $query->compile()->getCompiled();

        $results = $query
            ->enqueue((new Helper($this->connection))->showMeta())
            ->executeBatch()
        ;

        $sphinxResult = $results->getNext()
            ->fetchAllAssoc()
        ;
        $totalFound = $this->getTotalFoundFromMeta($results->getNext()->fetchAllAssoc());

        $ids = [];
        foreach ($sphinxResult as $result) {
            $ids[] = $result['id'];
        }

        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->entityManager->getRepository(Location::class);

        $locations = [];
        $place = $this->translator->trans('suggest.places');
        foreach ($ids as $id) {
            $details = $locationRepository->find($id);
            $adminUnit = $locationRepository->findAdminUnit($details->getAdmin1(), $details->getCountry()->getCountry());
            $locations[] = [
                'type' => $place,
                'id' => $details->getGeonameId(),
                'name' => $details->getName(),
                'latitude' => $details->getLatitude(),
                'longitude' => $details->getLongitude(),
                'admin1' => (null === $adminUnit) ? '' : $adminUnit->getName(),
                'country' => $details->getCountry()->getName(),
            ];
        }

        if ($totalFound > 20) {
            $locations[] = [
                'type' => 'refine',
                'text' => 'More results (' . ($totalFound - 20) . ')',
            ];
        }

        return [
            'locations' => $locations,
        ];
    }

    private function findCountryId(?string $country): ?string
    {
        if (null === $country) {
            return null;
        }

        if (strlen($country) === 2) {
            return $country;
        }

        $query = $this->sphinxQL
            ->select('*')
            ->from('geonames')
            ->match(['name'], $country)
            ->where('isCountry', '=', 1)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        $result = $query->execute()->fetchAssoc();

        if (null === $result) {
            return null;
        }

        // Return the first result
        return $result['country'];
    }

    private function findCountryOrAdminId(?string $countryOrAdmin): array
    {
        $countryId = $this->findCountryId($countryOrAdmin);
        if (null !== $countryId) {
            return [$countryId, null];
        }

        // Check if we find any match for the admin unit
        $query = $this->sphinxQL
            ->select('*')
            ->from('geonames')
            ->match(['name'], $countryOrAdmin)
            ->where('isAdmin', '=', 1)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        $result = $query->execute()->fetchAssoc();

        if (null === $result) {
            return [null, null];
        }

        // Return the first result including the country
        return [$result['country'], $result['admin1']];
    }

    private function findAdminUnitId(?string $countryId, ?string $adminUnit): ?string
    {
        if (null === $countryId || null === $adminUnit) {
            return null;
        }

        if ('' === $adminUnit) {
            return null;
        }

        $query = $this->sphinxQL
            ->select('*')
            ->from('geonames')
            ->match(['name', 'alternate'], $adminUnit)
            ->where('isadmin', '=', 1)
            ->where('country', '=', $countryId)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        $result = $query->execute()->fetchAssoc();

        if (null === $result) {
            return null;
        }

        // Return the first result
        return $result['admin1'];
    }

    private function getTotalFoundFromMeta(array $metaData)
    {
        $totalFound = 0;
        foreach ($metaData as $meta) {
            if ($meta['Variable_name'] == 'total_found') {
                $totalFound = $meta['Value'];
            }
        }

        return $totalFound;
    }
}
