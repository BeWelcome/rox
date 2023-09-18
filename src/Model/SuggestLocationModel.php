<?php

namespace App\Model;

use Doctrine\ORM\EntityManagerInterface;
use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\MatchBuilder;
use Foolz\SphinxQL\SphinxQL;
use Gedmo\Translatable\TranslatableListener;
use Manticoresearch\Client;
use Manticoresearch\Query;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\MatchPhrase;
use Manticoresearch\Query\MatchQuery;
use Manticoresearch\Search;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;

class SuggestLocationModel
{
    private const EXACT_PLACE = 'search.place.exact';
    private const PLACE = 'search.place';
    private const ADMIN_UNIT = 'search.admin.unit';
    private const COUNTRY = 'search.country';

    private const TYPE_PLACE = 'isplace';
    private const TYPE_ADMIN_UNIT = 'isadmin';
    private const TYPE_COUNTRY = 'iscountry';
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
        $places = $this->getPlaces($term, 'sum((min_hit_pos==1)*(exact_hit==1)*1000+population+member_count');

        return ['locations' => $places];
    }

    public function getSuggestionsForPlacesExact(string $term): array
    {
        return ['locations' => $this->getPlacesExact($term, 100, '')];
    }

    public function getSuggestionsForLocations(string $term): array
    {
        $placesExact = $this->getPlacesExact($term, 20, self::PLACE);
        $places = $this->getPlaces($term, 50);
        $adminUnits = $this->getAdminUnits($term, 10);
        $countries = $this->getCountries($term, 5);
        $results = array_merge($placesExact, $places, $adminUnits, $countries);

        return ['locations' => $this->removeDuplicates('id', $results)];
    }

    /**
     * Search term looks like this:
     *
     * place[[, admin unit], country]
     */
    private function getPlacesExact(string $term, int $limit, string $translationId): array
    {
        $parts = array_map('trim', explode(',', $term));

        $countryId = '';
        $adminUnits = [];
        if (1 < count($parts)) {
            $countryOrAdminUnit = end($parts);
            $countryId = $this->getCountryId($countryOrAdminUnit);

            $adminUnits = $this->searchAdminUnits(array_slice($parts, 1), $countryId);
        }

        $query = $this->getQueryForGeonamesRt();
        $matchQuery = new MatchPhrase($parts[0], 'name');
        $localeQuery = new BoolQuery();
        $localeQuery->should(new Equals('locale', '_geo'));
        $localeQuery->should(new Equals('locale', $this->translator->getLocale()));
        $filterElements = ['country', 'admin1', 'admin2', 'admin3', 'admin4'];
        $adminUnitFilterQuery = new BoolQuery();
        foreach ($adminUnits as $adminUnit) {
            $adminUnitFilter = new BoolQuery();
            foreach ($filterElements as $filterElement) {
                if (!empty($adminUnit[$filterElement])) {
                    $adminUnitFilter->must(new Equals($filterElement, $adminUnit[$filterElement]));
                }
            }
            $adminUnitFilterQuery->should($adminUnitFilter);
        }
        $boolQuery = new BoolQuery();
        $boolQuery->must($matchQuery);
        $boolQuery->must($localeQuery);
        if (!empty($adminUnits)) {
            $boolQuery->must($adminUnitFilterQuery);
        }

        $query
            ->search($boolQuery)
            ->filter('isPlace', 'equals', 1)
            ->option(
                'ranker',
                'expr(\'sum((min_hit_pos==1)*(exact_hit==1)*25 + (min_hit_pos==1) * 5 + (hit_count) * 3 + ' .
                '(population / 1000000) + (member_count / 10000)) \')'
            )
        ;
        if (!empty($countryId)) {
            $query->filter('country', 'equals', $countryId);
        }
        $results = $this->getManticoreResults($query, $limit);

        return $this->getLocationDetails($results, $translationId);
    }

    private function getPlaces(string $term, int $limit): array
    {
        $parts = array_map('trim', explode(',', $term));

        $countryId = '';
        $adminUnits = [];
        if (1 < count($parts)) {
            $countryOrAdminUnit = end($parts);
            $countryId = $this->getCountryId($countryOrAdminUnit);

            $adminUnits = $this->searchAdminUnits(array_slice($parts, 1), $countryId);
        }

        $query = $this->getQueryForGeonamesRt();
        $matchQuery = new MatchQuery($parts[0] . '*', 'name');
        $localeQuery = new BoolQuery();
        $localeQuery->should(new Equals('locale', '_geo'));
        $localeQuery->should(new Equals('locale', $this->translator->getLocale()));
        $filterElements = ['country', 'admin1', 'admin2', 'admin3', 'admin4'];
        $adminUnitFilterQuery = new BoolQuery();
        foreach ($adminUnits as $adminUnit) {
            $adminUnitFilter = new BoolQuery();
            foreach ($filterElements as $filterElement) {
                if (!empty($adminUnit[$filterElement])) {
                    $adminUnitFilter->must(new Equals($filterElement, $adminUnit[$filterElement]));
                }
            }
            $adminUnitFilterQuery->should($adminUnitFilter);
        }
        $boolQuery = new BoolQuery();
        $boolQuery->must($matchQuery);
        $boolQuery->must($localeQuery);
        if (!empty($adminUnits)) {
            $boolQuery->must($adminUnitFilterQuery);
        }

        $query
            ->search($boolQuery)
            ->filter('isPlace', 'equals', 1)
            ->option(
                'ranker',
                'expr(\'sum((min_hit_pos==1)*(exact_hit==1)*25 + (min_hit_pos==1) * 5 + (hit_count) * 3 + ' .
                        '(population / 1000000) + (member_count / 10000))\')'
            )
        ;
        if (!empty($countryId)) {
            $query->filter('country', 'equals', $countryId);
        }
        $results = $this->getManticoreResults($query, $limit);

        return $this->getLocationDetails($results, self::PLACE);
    }

    private function getAdminUnits(string $term, int $limit = 10): array
    {
        $parts = array_map('trim', explode(',', $term));

        $countryId = '';
        $adminUnits = [];
        if (1 < count($parts)) {
            $countryOrAdminUnit = end($parts);
            $countryId = $this->getCountryId($countryOrAdminUnit);

            $adminUnits = $this->searchAdminUnits(array_slice($parts, 1), $countryId);
        }

        $query = $this->getQueryForGeonamesRt();
        $matchQuery = new MatchQuery($parts[0] . '*', 'name');
        $localeQuery = new BoolQuery();
        $localeQuery->should(new Equals('locale', '_geo'));
        $localeQuery->should(new Equals('locale', $this->translator->getLocale()));
        $filterElements = ['country', 'admin1', 'admin2', 'admin3', 'admin4'];
        $adminUnitFilterQuery = new BoolQuery();
        foreach ($adminUnits as $adminUnit) {
            $adminUnitFilter = new BoolQuery();
            foreach ($filterElements as $filterElement) {
                if (!empty($adminUnit[$filterElement])) {
                    $adminUnitFilter->must(new Equals($filterElement, $adminUnit[$filterElement]));
                }
            }
            $adminUnitFilterQuery->should($adminUnitFilter);
        }
        $boolQuery = new BoolQuery();
        $boolQuery->must($matchQuery);
        $boolQuery->must($localeQuery);
        if (!empty($adminUnits)) {
            $boolQuery->must($adminUnitFilterQuery);
        }

        $query
            ->search($boolQuery)
            ->filter('isAdmin', 'equals', 1)
            ->option(
                'ranker',
                'expr(\'sum((min_hit_pos==1)*(exact_hit==1)*25 + (min_hit_pos==1) * 5 + (hit_count) * 3)\')'
            )
        ;
        if (!empty($countryId)) {
            $query->filter('country', 'equals', $countryId);
        }
        $results = $this->getManticoreResults($query, $limit);

        return $this->getLocationDetails($results, self::ADMIN_UNIT);
    }

    private function getCountries(string $term, int $limit = 3): array
    {
        $parts = array_map('trim', explode(',', $term));

        if (1 !== count($parts)) {
            return [];
        }

        $query = $this->getQueryForGeonamesRt();
        $matchQuery = new MatchQuery($parts[0] . '*', 'name');
        $localeQuery = new BoolQuery();
        $localeQuery->should(new Equals('locale', '_geo'));
        $localeQuery->should(new Equals('locale', $this->translator->getLocale()));
        $boolQuery = new BoolQuery();
        $boolQuery->must($matchQuery);
        $boolQuery->must($localeQuery);

        $query
            ->search($boolQuery)
            ->filter('isCountry', 'equals', 1)
            ->option(
                'ranker',
                'expr(\'sum((min_hit_pos==1)*(exact_hit==1)*25 + (min_hit_pos==1) * 5 + (hit_count) * 3)\')'
            )
        ;
        $results = $this->getManticoreResults($query, $limit);

        return $this->getLocationDetails($results, self::COUNTRY);
    }

    private function getCountryId(string $countryOrAdminUnit): ?string
    {
        if (2 === \strlen($countryOrAdminUnit)) {
            return $countryOrAdminUnit;
        }

        $query = $this->getQueryForGeonamesRt();
        $query
            ->match("{$countryOrAdminUnit}")
            ->filter('iscountry', 'equals', 1)
            ->option('ranker', 'expr(\'sum(exact_hit)\')')
            ->orFilter('locale', 'equals', $this->translator->getLocale())
            ->orFilter('locale', 'equals', '_geo');

        $countries = $this->getManticoreResults($query, 5);

        if (1 <> count($countries)) {
            return null;
        }

        // Return the only result.
        $country = reset($countries);
        return $country['country'];
    }

    public function getLocationDetails(array $results, string $typeTranslationId = null): array
    {
        $locale = $this->translator->getLocale();
        $type = $typeTranslationId ?? $this->translator->trans($typeTranslationId);
        $locations = [];
        foreach ($results as $location) {
            $locationEntity = $this->getDetailsForId($location['geoname_id']);
            if (null !== $locationEntity) {
                $name = $locationEntity->getName();
                $admin1 = $locationEntity->getAdmin1();
                if (null !== $admin1 && $locationEntity !== $admin1) {
                    $admin1->setTranslatableLocale($locale);
                    $this->entityManager->refresh($admin1);
                    $name .= '#' . $admin1->getName();
                }
                $country = $locationEntity->getCountry();
                if (null !== $country && $locationEntity !== $country) {
                    $country->setTranslatableLocale($locale);
                    $this->entityManager->refresh($country);
                    $name .= '#' . $country->getName();
                }

                $locations[] = [
                    'type' => $type,
                    'isAdminUnit' => $location['isadmin'] || $location['iscountry'],
                    'id' => $locationEntity->getGeonameId(),
                    'name' => $name,
                    'latitude' => $locationEntity->getLatitude(),
                    'longitude' => $locationEntity->getLongitude(),
                ];
            }
        }

        return $locations;
    }

    private function getPlaceInformation(string $term): array
    {
        $placeAdminOrCountry = explode(',', $term);
        if (count($placeAdminOrCountry) > 3) {
            return ['locations' => []];
        }

        $adminIds = [];
        $countryId = null;
        $place = trim($placeAdminOrCountry[0]);
        if (2 === count($placeAdminOrCountry)) {
            $country = trim($placeAdminOrCountry[1]);
            $countryId = $this->findCountryId($country);
        }

        if (3 === count($placeAdminOrCountry)) {
            $admin = trim($placeAdminOrCountry[1]);
            $country = trim($placeAdminOrCountry[2]);
            $countryId = $this->findCountryId($country);
            $adminIds = $this->findAdminUnitIds($admin, $countryId);
        }

        return [
            count($placeAdminOrCountry) > 1 ? $place : $term,
            $adminIds,
            $countryId,
        ];
    }

    private function getDetailsForId($id)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->select('l')
            ->from('App\Entity\NewLocation', 'l')
            ->where($qb->expr()->eq('l.geonameId', $id))
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

    public function removeDuplicates(string $key, ...$resultArrays): array
    {
        $geonameIds = [];
        $places = [];
        foreach ($resultArrays as $results) {
            foreach ($results as $result) {
                if (!\in_array($result[$key], $geonameIds, true)) {
                    $geonameIds[] = $result[$key];
                    $places[] = $result;
                }
            }
        }

        return $places;
    }

    private function findAdminUnits(string $term, int $count): array
    {
        $adminUnits = $this->queryManticore($term, self::TYPE_ADMIN_UNIT);
        $result = $this->removeDuplicates('geoname_id', $adminUnits);

        return array_slice($result, 0, $count);
    }

    private function findCountries(string $term, int $count): array
    {
        $countries = $this->getIdsForLocationType($term, self::TYPE_COUNTRY);
        $result = $this->removeDuplicates('geoname_id', $countries);

        return array_slice($result, 0, $count);
    }

    private function getIdsForLocationType(
        array $locationParts,
        ?string $ranker = null
    ): array {
        $resultsForLocale = $this->queryManticore(
            $locationParts,
            $ranker,
            $this->translator->getLocale()
        );

        $results = $this->queryManticore(
            $locationParts,
            $ranker,
            null
        );

        return $this->removeDuplicates('geoname_id', $resultsForLocale, $results);
    }

    private function getCountryIds(string $country): array
    {
        $query = $this->getQueryForGeonamesRt();
        $query
            ->match("{$country}")
            ->filter('iscountry', 'equals', 1)
            ->option('ranker', 'expr(\'sum(exact_hit)\')');
        ;

        $query
            ->orFilter('locale', 'equals', $this->translator->getLocale())
            ->orFilter('locale', 'equals', '_geo');

        return $this->getManticoreResults($query);
    }

    private function searchAdminUnits(array $adminUnits, ?string $countryId): array
    {
        if (null !== $countryId) {
            $adminUnits = array_slice($adminUnits, 0, -1);
        }

        if (empty($adminUnits)) {
            return [];
        }

        $results = [];
        $adminUnitIds = ['country' => $countryId ?? '', 'admin1' => '', 'admin2' => '', 'admin3' => '', 'admin4' => ''];
        $adminUnits = array_reverse($adminUnits);
        $countOfAdminUnits = count($adminUnits) - 1;
        for ($index = 0; $index <= $countOfAdminUnits; $index++) {
            $adminUnit = $adminUnits[$index];
            $query = $this->getQueryForGeonamesRt();
            $query
                ->match("{$adminUnit}")
                ->filter('isadmin', 'equals', 1)
                ->option('ranker', 'expr(\'sum(exact_hit)\')');

            foreach ($adminUnitIds as $level => $adminUnitId) {
                if (!empty($adminUnitId)) {
                    $query->filter($level, 'equals', $adminUnitId);
                }
            }

            $results = $this->getManticoreResults($query);
            if (0 === count($results)) {
                // Either there is no admin unit with that name or the sequence is wrong. \todo error handling?
                return [];
            }
            if ($index !== $countOfAdminUnits) {
                if (1 === count($results)) {
                    // Limit the next search to the found admin unit
                    $foundAdminUnit = reset($results);
                    if (null === $countryId) {
                        $adminUnitIds['country'] = $foundAdminUnit['country'];
                    }
                    for ($level = 1; $level <= 4; $level++) {
                        $adminUnitIds['admin' . $level] = $foundAdminUnit['admin' . $level];
                    }
                } else {
                    // more than one admin unit found in the middle of the sequence isn't allowed
                    return [];
                }
            }
        }

        return $results;
    }

    private function queryManticore(
        array $locationParts,
        string $type,
        ?string $ranker = null
    ): array {
        if (empty($locationParts)) {
            return [];
        }

        $query = $this->getQueryForGeonamesRt();
        $query
            ->phrase("{$locationParts[0]}")
            ->filter($type, 'equals', 1)
        ;

        if (null !== $ranker) {
            $query->option('ranker', 'expr(\'' . $ranker . '\')');
        }

        $adminIds = array_chunk($locationParts, 1);
        if (!empty($adminIds)) {
            foreach ($adminIds as $adminId)
            {
                $query->orFilter('admin', 'equals', $adminId);
            }
        }

        if (null !== $countryId) {
            $query->filter('country', 'equals', $countryId);
        }

        if (null !== $locale) {
            $query->filter('locale', 'equals', $locale);
        } else {
            $query->filter('locale', 'equals', '_geo');
        }

        $results = $query->get();
        $results->rewind();

        $manticoreResult = [];
        while ($results->valid()) {
            $hit = $results->current();
            if ($hit->getScore() > 0) {
                $data = $hit->getData();
                $manticoreResult[] = $data;
            }
            $results->next();
        }

        return $manticoreResult;
    }

    private function getManticoreResults(Search $query, int $limit): array
    {
        $query->limit($limit);
        $results = $query->get();
        $results->rewind();

        $manticoreResult = [];
        while ($results->valid()) {
            $hit = $results->current();
            $data = $hit->getData();
            if ($hit->getScore() > 0 && !isset($manticoreResult[$data['geoname_id']])) {
                $manticoreResult[$data['geoname_id']] = $data;
            }
            $results->next();
        }

        return $manticoreResult;
    }

    private function getQueryForGeonamesRt(): Search
    {
        $config = ['host' => '127.0.0.1','port' => 9412];
        $client = new Client($config);
        $query = new Search($client);
        $query
            ->setIndex('geonames_rt')
            ->orFilter('locale', 'equals', $this->translator->getLocale())
            ->orFilter('locale', 'equals', '_geo');

        return $query;
    }
}
