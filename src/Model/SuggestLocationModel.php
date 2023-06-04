<?php

namespace App\Model;

use Doctrine\ORM\EntityManagerInterface;
use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\MatchBuilder;
use Foolz\SphinxQL\SphinxQL;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Contracts\Translation\TranslatorInterface;
use function count;

class SuggestLocationModel
{
    private const PLACE = 'search.place';
    private const ADMIN_UNIT = 'search.admin.unit';
    private const COUNTRY = 'search.country';

    private const TYPE_PLACE = 'isPlace';
    private const TYPE_ADMIN_UNIT = 'isAdmin';
    private const TYPE_COUNTRY = 'isCountry';
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private SphinxQL $sphinxQL;
    private Connection $connection;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->connection = new Connection();
        $this->connection->setParams(['host' => 'localhost', 'port' => 9306]);

        $this->sphinxQL = new SphinxQL($this->connection);
    }

    public function getSuggestionsForPlaces(string $term): array
    {
        return $this->getPlaces($term, false);
    }

    public function getSuggestionsForExactPlaces(string $term): array
    {
        return $this->getPlaces($term, true);
    }

    public function getSuggestionsForLocations(string $term): array
    {
        return $this->getLocations($term);
    }

    /**
     * Search term looks like this:.
     *
     * place[[, admin unit], admin unit|country]
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getPlaces(string $term, bool $preferExact, int $count = 20): array
    {
        $term = trim($term, ' ,');
        list($place, $countryId, $adminId) = $this->getPlaceInformation($term);

        if ($place !== trim($term) && null === $adminId && null === $countryId) {
            return ['locations' => []];
        }

        list($totalFound, $sphinxResult) =
            $this->getIdsForLocationType($place, self::TYPE_PLACE, $count, $countryId, $adminId, $preferExact);

        if (0 === $totalFound) {
            return ['locations' => []];
        }

        return $this->getLocationDetails($sphinxResult, $totalFound, $count, self::PLACE);
    }

    /**
     * Search term looks like this:.
     *
     * place[[, admin unit], admin unit|country]
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getLocations(string $term): array
    {
        $parts = explode(',', $term);
        $result = $this->getPlaces($term, false, 10);

        // In case no optional part was given search for admin units and countries
        if (1 == count($parts)) {
            $adminUnits = $this->findAdminUnits($term, 5);
            if (!empty($adminUnits['locations'])) {
                $result['locations'] = array_merge($result['locations'], $adminUnits['locations']);
            }

            $countries = $this->findCountries($term, 2);
            if (!empty($countries['locations'])) {
                $result['locations'] = array_merge($result['locations'], $countries['locations']);
            }
        }

        return $result;
    }

    private function findCountryId(?string $country): ?string
    {
        if (null === $country) {
            return null;
        }

        if (2 === \strlen($country)) {
            return $country;
        }

        // Get First element of found ids
        $countryIds = $this->findCountryIds($country);
        if (empty($countryIds) || count($countryIds) > 1) {
            return null;
        }

        return $countryIds[0]['country'];
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function findCountryIds(?string $country): ?array
    {
        if (null === $country) {
            return [];
        }

        if (2 === \strlen($country)) {
            return [ 'country' => $country];
        }

        $match = (new MatchBuilder($this->sphinxQL))
            ->exact(SphinxQL::expr($country))
        ;

        $query = $this->sphinxQL
            ->select('country')
            ->from('geonames_sphinx')
            ->match($match)
            ->where(self::TYPE_COUNTRY, '=', 1)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        $result = $query->execute()->fetchAllAssoc();

        return $result;
    }

    private function findAdminUnitId(?string $countryId, ?string $adminUnit): ?string
    {
        if (null === $adminUnit) {
            return null;
        }

        if ('' === $adminUnit) {
            return null;
        }

        $adminUnits = $this->findAdminUnitIds($adminUnit, $countryId);
        if (null === $adminUnits || count($adminUnits) > 1) {
            return null;
        }

        return $adminUnits[0];
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function findAdminUnitIds(string $adminUnit, ?string $countryId = null): ?array
    {
        if (null === $adminUnit) {
            return null;
        }

        if ('' === $adminUnit) {
            return null;
        }
        $match = (new MatchBuilder($this->sphinxQL))
            ->exact(SphinxQL::expr($adminUnit))
            ->orMatch(SphinxQL::expr($adminUnit))
            ->orMatch(SphinxQL::expr($adminUnit . '*'))
        ;

        $query = $this->sphinxQL
            ->select('*')
            ->from('geonames_sphinx')
            ->match($match)
            ->where(self::TYPE_ADMIN_UNIT, '=', 1)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        if (null !== $countryId) {
            $query->where('country', '=', $countryId);
        }

        $result = $query->execute()->fetchAllAssoc();

        return $result;
    }

    private function getTotalFoundFromMeta(array $metaData)
    {
        $totalFound = 0;
        foreach ($metaData as $meta) {
            if ('total_found' === $meta['Variable_name']) {
                $totalFound = $meta['Value'];
            }
        }

        return $totalFound;
    }

    public function getLocationDetails(array $sphinxResult, int $totalFound, int $count, string $type): array
    {
        $ids = $this->filterForUniqueIds($sphinxResult);

        $locale = $this->translator->getLocale();
        $result = [];
        foreach ($ids as $id) {
            $location = $this->getDetailsForId($id);
            $admin1 = $location->getAdmin1();
            if (null !== $admin1) {
                $admin1->setTranslatableLocale($locale);
                $this->entityManager->refresh($admin1);
            }
            $country = $location->getCountry();
            $country->setTranslatableLocale($locale);
            $this->entityManager->refresh($country);
            $result[] = [
                'type' => $this->translator->trans($type, [], 'messages'),
                'id' => $location->getGeonameId(),
                'name' => $location->getName(),
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
                'admin1' => (null === $admin1 || $location === $admin1) ? '' : $location->getAdmin1()->getName(),
                'country' => ($type === self::COUNTRY) ? '' : $location->getCountry()->getName(),
            ];
        }

        if ($totalFound > $count) {
            $result[] = [
                'type' => 'refine',
                'title' => $this->translator->trans('suggest.refine'),
                'text' => $this->translator->trans('suggest.more.results'),
            ];
        }

        return [
            'locations' => $result,
        ];
    }

    private function searchForPlace(
        string $place,
        ?string $countryId,
        ?string $adminId,
        bool $preferExact,
        int $count = 20
    ): array {
        list(, $resultsForLocale) = $this->executeSphinxQLQuery(
            $place,
            self::TYPE_PLACE,
            $countryId,
            $adminId,
            $this->translator->getLocale(),
            $preferExact,
            $count
        );

        list($found, $results) = $this->executeSphinxQLQuery($place, self::TYPE_PLACE, $countryId, $adminId, null, $preferExact, $count);

        return $this->removeDuplicates($resultsForLocale, $found, $results, $count);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function executeSphinxQLQuery(
        string $place,
        string $type,
        ?string $countryId,
        ?string $adminId,
        ?string $locale,
        bool $preferExact,
        int $count
    ): array {
        $match = (new MatchBuilder($this->sphinxQL))
            ->exact(SphinxQL::expr($place))
        ;
        if (!$preferExact) {
            $match
                ->orMatch(SphinxQL::expr($place))
                ->orMatch(SphinxQL::expr($place . '*'))
            ;
        }

        $query = $this->sphinxQL->select('geonameid', 'admin1', 'country', SphinxQL::expr('WEIGHT() As w'))
            ->from('geonames_sphinx')
            ->match($match)
            ->where($type, '=', 1)
            ->where('w', '>', 0)
            ->orderBy('w', 'DESC')
            ->limit(0, $count)
        ;

        if ($preferExact) {
            $ranker = 'expr(\'sum(((min_hit_pos==1)+exact_hit)*100000+population)\')';
        } else {
            $ranker =  'expr(\'sum((min_hit_pos==1)*50+exact_hit*100)+membercount\')';
        }
        $query->option('ranker', SphinxQL::expr($ranker));

        if (null !== $locale) {
            $query->where('language', '=', $locale);
        }

        if (null !== $adminId) {
            $query->where('admin1', '=', $adminId);
        }

        if (null !== $countryId) {
            $query->where('country', '=', $countryId);
        }

        $results = $query
            ->enqueue((new Helper($this->connection))->showMeta())
            ->executeBatch()
        ;

        $sphinxResult = $results->getNext()
            ->fetchAllAssoc()
        ;
        $totalFound = $this->getTotalFoundFromMeta($results->getNext()->fetchAllAssoc());

        return [
            (int) $totalFound,
            $sphinxResult,
        ];
    }

    private function getPlaceInformation(string $term): array
    {
        $placeAdminOrCountry = explode(',', $term);
        if (count($placeAdminOrCountry) > 3) {
            return ['locations' => []];
        }

        $adminId = null;
        $countryId = null;
        $place = trim($placeAdminOrCountry[0]);
        if (2 === count($placeAdminOrCountry)) {
            $countryOrAdminUnit = trim($placeAdminOrCountry[1]);
            $countryId = $this->findCountryId($countryOrAdminUnit);
            if (null === $countryId) {
                $adminId = $this->findAdminUnitId(null, $countryOrAdminUnit);
            }
        }

        if (3 === count($placeAdminOrCountry)) {
            $admin = trim($placeAdminOrCountry[1]);
            $country = trim($placeAdminOrCountry[2]);
            $countryId = $this->findCountryId($country);
            $adminId = $this->findAdminUnitId($countryId, $admin);
        }

        return [
            count($placeAdminOrCountry) > 1 ? $place : $term,
            $countryId,
            $adminId,
        ];
    }

    private function getDetailsForId($id)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->select('l')
            ->from('App\Entity\NewLocation', 'l')
            ->where($qb->expr()->in('l.geonameId', $id))
            ->getQuery()
        ;
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $query->setHint(
            TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            $this->translator->getLocale(),
        );
        // fallback
        $query->setHint(
            TranslatableListener::HINT_FALLBACK,
            1 // fallback to default values in case if record is not translated
        );

        return $query->getOneOrNullResult();
    }

    /**
     * @param     $resultsForLocale
     * @param     $found
     * @param     $results
     * @param int $count
     *
     * @return array
     */
    public function removeDuplicates($resultsForLocale, $found, $results, int $count): array
    {
        // remove duplicates and set $totalFound to the remaining hits.
        $geonameIds = [];
        $places = [];
        foreach ($resultsForLocale as $result) {
            if (!\in_array($result['geonameid'], $geonameIds, true)) {
                $geonameIds[] = $result['geonameid'];
                $places[] = $result;
            }
        }

        if (0 !== $found) {
            foreach ($results as $result) {
                if (!\in_array($result['geonameid'], $geonameIds, true)) {
                    $geonameIds[] = $result['geonameid'];
                    $places[] = $result;
                }
            }
        }

        // return the first $count
        $found = count($places);
        $places = \array_slice($places, 0, $count);

        return [
            $found,
            $places,
        ];
    }

    private function getCountries(string $term, int $count): array
    {
        $result = [];

        return $result;
    }

    private function filterForUniqueIds($sphinxResult): array
    {
        $ids = [];
        foreach ($sphinxResult as $result) {
            if (!\in_array($result['geonameid'], $ids, true)) {
                $ids[] = $result['geonameid'];
            }
        }

        return $ids;
    }

    private function findAdminUnits(string $term, int $count)
    {
        list($totalFound, $sphinxResult) = $this->getIdsForLocationType($term, self::TYPE_ADMIN_UNIT, $count);

        return $this->getLocationDetails($sphinxResult, $totalFound, $count, self::ADMIN_UNIT);
    }
    private function findCountries(string $term, int $count)
    {
        list($totalFound, $sphinxResult) = $this->getIdsForLocationType($term, self::TYPE_COUNTRY, $count);

        return $this->getLocationDetails($sphinxResult, $totalFound, $count, self::COUNTRY);
    }

    private function getIdsForLocationType(
        string $term,
        string $type,
        int $count = 20,
        ?string $countryId = null,
        ?string $adminId = null,
        bool $preferExact = true
    ): array {
        list(, $resultsForLocale) = $this->executeSphinxQLQuery(
            $term,
            $type,
            $countryId,
            $adminId,
            $this->translator->getLocale(),
            $preferExact,
            $count
        );

        list($found, $results) = $this->executeSphinxQLQuery(
            $term,
            $type,
            $countryId,
            $adminId,
            null,
            $preferExact,
            $count
        );

        return $this->removeDuplicates($resultsForLocale, $found, $results, $count);
    }
}
