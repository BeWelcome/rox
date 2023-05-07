<?php

namespace App\Model;

use App\Entity\NewLocation;
use Doctrine\ORM\EntityManagerInterface;
use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\MatchBuilder;
use Foolz\SphinxQL\SphinxQL;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuggestLocationModel
{
    private const PLACE = 'place';
    private const ADMIN_UNIT = 'admin.unit';
    private const COUNTRY = 'country';

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

        list($totalFound, $sphinxResult) = $this->searchForPlace($place, $countryId, $adminId, $preferExact);

        if (0 === $totalFound) {
            return ['locations' => []];
        }

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
                'type' => self::PLACE,
                'id' => $location->getGeonameId(),
                'name' => $location->getName(),
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
                'admin1' => (null === $admin1) ? '' : $location->getAdmin1()->getName(),
                'country' => $location->getCountry()->getName(),
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

    /**
     * Search term looks like this:.
     *
     * place[[, admin unit], admin unit|country]
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getLocations(string $term): array
    {
        $result = $this->getPlaces($term, false, 10);

        $result['locations'][] = $this->getAdminUnits($term, 5);

        $result['locations'][] = $this->getCountries($term, 2);

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
        if (empty($countryIds)) {
            return null;
        }

        return reset($countryIds);
    }
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function findCountryIds(?string $country): array
    {
        if (null === $country) {
            return [];
        }

        if (2 === \strlen($country)) {
            return [ 'country' => $country];
        }

        $match = (new MatchBuilder($this->sphinxQL))
            ->exact(SphinxQL::expr($country))
            ->orMatch(SphinxQL::expr($country))
            ->orMatch(SphinxQL::expr($country . '*'))
        ;

        $query = $this->sphinxQL
            ->select('country')
            ->from('geonames_sphinx')
            ->match($match)
            ->where('isCountry', '=', 1)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        $sql = $query->compile()->getCompiled();

        $result = $query->execute()->fetchAssoc();

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function findCountryOrAdminIds(?string $countryOrAdmin): array
    {
        $countryId = $this->findCountryIds($countryOrAdmin);
        if (null !== $countryId) {
            return [$countryId, null];
        }

        $match = (new MatchBuilder($this->sphinxQL))
            ->exact(SphinxQL::expr($countryOrAdmin))
            ->orMatch(SphinxQL::expr($countryOrAdmin))
            ->orMatch(SphinxQL::expr($countryOrAdmin . '*'))
        ;

        // Check if we find any match for the admin unit
        $query = $this->sphinxQL
            ->select('*')
            ->from('geonames_sphinx')
            ->match($match)
            ->where('isAdmin', '=', 1)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        $result = $query->execute()->fetchAssoc();

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function findAdminUnitId(?string $countryId, ?string $adminUnit): ?string
    {
        if (null === $countryId || null === $adminUnit) {
            return null;
        }

        if ('' === $adminUnit) {
            return null;
        }

        $adminUnits = $this->findAdminUnitIds($countryId, $adminUnit);
        if (null === $adminUnits) {
            return null;
        }

        return reset($adminUnits);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function findAdminUnitIds(?string $countryId, ?string $adminUnit): ?array
    {
        if (null === $countryId || null === $adminUnit) {
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
            ->where('isadmin', '=', 1)
            ->where('country', '=', $countryId)
            ->option('ranker', SphinxQL::expr('expr(\'sum(exact_hit*1000)\')'))
        ;

        $result = $query->execute()->fetchAssoc();

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

    private function searchForPlace(
        string $place,
        ?string $countryId,
        ?string $adminId,
        bool $preferExact,
        int $count = 20
    ): array {
        list(, $resultsForLocale) = $this->executeSphinxQLQuery(
            $place,
            $countryId,
            $adminId,
            $this->translator->getLocale(),
            $preferExact,
            $count
        );

        list($found, $results) = $this->executeSphinxQLQuery($place, $countryId, $adminId, null, $preferExact, $count);

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
        $found = \count($places);
        $places = \array_slice($places, 0, $count);

        return [
            $found,
            $places,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function executeSphinxQLQuery(
        string $place,
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
            ->where('isPlace', '=', 1)
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
        if (\count($placeAdminOrCountry) > 3) {
            return ['locations' => []];
        }

        $adminId = null;
        $countryId = null;
        $place = trim($placeAdminOrCountry[0]);
        if (2 === \count($placeAdminOrCountry)) {
            $countryOrAdmin = trim($placeAdminOrCountry[1]);
            $possibleUnits = $this->findCountryOrAdminIds($countryOrAdmin);
        }

        if (3 === \count($placeAdminOrCountry)) {
            $admin = trim($placeAdminOrCountry[1]);
            $country = trim($placeAdminOrCountry[2]);
            $countryId = $this->findCountryId($country);
            $adminId = $this->findAdminUnitId($countryId, $admin);
        }

        return [
            \count($placeAdminOrCountry) > 1 ? $place : $term,
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

    private function getAdminUnits(string $term, int $count): array
    {
        $result = [];

        return $result;
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
}
