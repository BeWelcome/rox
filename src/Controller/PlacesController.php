<?php

namespace App\Controller;

use App\Model\PlacesModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The page of one country: its regions (to filter on) and its members.
 * The list of all countries is the "Explore by country" block of the home page.
 */
class PlacesController extends AbstractController
{
    private const int MEMBERS_PER_PAGE = 20;

    public function __construct(
        private readonly PlacesModel $placesModel,
    ) {
    }

    /**
     * The list of all countries is now the "Explore by country" block of the home page.
     */
    #[Route(path: '/places', name: 'places', methods: ['GET'])]
    public function countries(): Response
    {
        return $this->redirect($this->generateUrl('landingpage') . '#countries');
    }

    #[Route(
        path: '/places/{countryname}/{countrycode}',
        name: 'places_country',
        requirements: ['countrycode' => '[A-Za-z]{2}'],
        methods: ['GET'],
    )]
    public function country(
        Request $request,
        string $countrycode,
        #[MapQueryParameter] ?string $region = null,
        #[MapQueryParameter] ?int $city = null,
        #[MapQueryParameter] int $page = 1,
    ): Response {
        $locale = $request->getLocale();
        $country = $this->placesModel->getCountry($countrycode, $locale);
        if (null === $country) {
            throw $this->createNotFoundException();
        }

        $regions = $this->placesModel->getRegions($country['code'], $locale);
        // A town (?city=, the town of a profile): its region is the region shown as chosen.
        $activeCity = null === $city ? null : $this->placesModel->getCity($city, $country['code'], $locale);
        if (null !== $activeCity && null !== $activeCity['regionCode']) {
            $region = $activeCity['regionCode'];
        }
        $activeRegion = null !== $region && isset($regions[$region]) ? $regions[$region] : null;

        $result = $this->placesModel->getMembers(
            $country['code'],
            $activeRegion['code'] ?? null,
            max(1, $page),
            self::MEMBERS_PER_PAGE,
            $activeCity['geonameId'] ?? null
        );
        $pages = max(1, (int) ceil($result['total'] / self::MEMBERS_PER_PAGE));
        if ($page > $pages) {
            return $this->redirectToRoute('places_country', array_filter([
                'countryname' => $country['slug'],
                'countrycode' => $country['code'],
                'region' => null === $activeCity ? ($activeRegion['code'] ?? null) : null,
                'city' => $activeCity['geonameId'] ?? null,
                'page' => $pages > 1 ? $pages : null,
            ]));
        }

        // "Search hosts in ..." opens the member search for the country, the chosen region or the town.
        $place = $activeCity ?? $activeRegion ?? $country;

        return $this->render('places/country.html.twig', [
            'country' => $country,
            'country_total' => $activeRegion ? $this->placesModel->countMembers($country['code'], null) : $result['total'],
            'regions' => $regions,
            'active_region' => $activeRegion,
            'active_city' => $activeCity,
            'members' => $result['members'],
            'members_total' => $result['total'],
            'comment_counts' => $this->placesModel->getCommentCounts($result['members']),
            'page' => max(1, $page),
            'pages' => $pages,
            'search_query' => ['tiny' => [
                'location' => $place['name'],
                'location_fullname' => implode(', ', array_filter([$activeCity['name'] ?? null, $activeRegion['name'] ?? null, $country['name']])),
                'location_name' => $place['name'],
                'location_geoname_id' => $place['geonameId'],
                'location_latitude' => $place['latitude'],
                'location_longitude' => $place['longitude'],
                // A town is searched around it, a country or a region as a whole
                'location_admin_unit' => null === $activeCity ? 1 : 0,
            ]],
        ]);
    }

    /**
     * Old address of the following pages of a country.
     */
    #[Route(
        path: '/places/{countryname}/{countrycode}/page/{page}',
        name: 'places_country_page',
        requirements: ['countrycode' => '[A-Za-z]{2}', 'page' => '\d+'],
        methods: ['GET'],
    )]
    public function countryPage(string $countryname, string $countrycode, int $page): Response
    {
        return $this->redirectToRoute('places_country', [
            'countryname' => $countryname,
            'countrycode' => $countrycode,
            'page' => $page,
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Old address of a region (its geonames id): the country page with that region chosen.
     */
    #[Route(
        path: '/places/{countryname}/{countrycode}/{regionname}/{regioncode}',
        name: 'places_region',
        requirements: ['countrycode' => '[A-Za-z]{2}', 'regioncode' => '\\d+|\\*'],
        methods: ['GET'],
    )]
    #[Route(
        path: '/places/{countryname}/{countrycode}/{regionname}/{regioncode}/page/{page}',
        name: 'places_region_page',
        requirements: ['countrycode' => '[A-Za-z]{2}', 'regioncode' => '\\d+|\\*', 'page' => '\\d+'],
        methods: ['GET'],
    )]
    public function region(Request $request, string $countryname, string $countrycode, string $regioncode): Response
    {
        $code = null;
        foreach ($this->placesModel->getRegions(strtoupper($countrycode), $request->getLocale()) as $region) {
            if ((string) $region['geonameId'] === $regioncode) {
                $code = $region['code'];
            }
        }

        return $this->redirectToRoute('places_country', array_filter([
            'countryname' => $countryname,
            'countrycode' => strtoupper($countrycode),
            'region' => $code,
        ]), Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Address of a town (the town of a profile): the country page with its members.
     */
    #[Route(
        path: '/places/{countryname}/{countrycode}/{regionname}/{regioncode}/{cityname}/{citycode}',
        name: 'places_city',
        requirements: ['countrycode' => '[A-Za-z]{2}', 'citycode' => '\\d+'],
        methods: ['GET'],
    )]
    #[Route(
        path: '/places/{countryname}/{countrycode}/{regionname}/{regioncode}/{cityname}/{citycode}/page/{page}',
        name: 'places_city_page',
        requirements: ['countrycode' => '[A-Za-z]{2}', 'citycode' => '\\d+', 'page' => '\\d+'],
        methods: ['GET'],
    )]
    public function city(string $countryname, string $countrycode, int $citycode): Response
    {
        return $this->redirectToRoute('places_country', [
            'countryname' => $countryname,
            'countrycode' => strtoupper($countrycode),
            'city' => $citycode,
        ]);
    }
}
