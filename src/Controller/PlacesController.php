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
        #[MapQueryParameter] int $page = 1,
    ): Response {
        $locale = $request->getLocale();
        $country = $this->placesModel->getCountry($countrycode, $locale);
        if (null === $country) {
            throw $this->createNotFoundException();
        }

        $regions = $this->placesModel->getRegions($country['code'], $locale);
        $activeRegion = null !== $region && isset($regions[$region]) ? $regions[$region] : null;

        $result = $this->placesModel->getMembers(
            $country['code'],
            $activeRegion['code'] ?? null,
            max(1, $page),
            self::MEMBERS_PER_PAGE
        );
        $pages = max(1, (int) ceil($result['total'] / self::MEMBERS_PER_PAGE));
        if ($page > $pages) {
            return $this->redirectToRoute('places_country', array_filter([
                'countryname' => $country['slug'],
                'countrycode' => $country['code'],
                'region' => $activeRegion['code'] ?? null,
                'page' => $pages > 1 ? $pages : null,
            ]));
        }

        // "Search hosts in ..." opens the member search for the country, or for the chosen region.
        $place = $activeRegion ?? $country;

        return $this->render('places/country.html.twig', [
            'country' => $country,
            'country_total' => $activeRegion ? $this->placesModel->countMembers($country['code'], null) : $result['total'],
            'regions' => $regions,
            'active_region' => $activeRegion,
            'members' => $result['members'],
            'members_total' => $result['total'],
            'page' => max(1, $page),
            'pages' => $pages,
            'search_query' => ['tiny' => [
                'location' => $place['name'],
                'location_fullname' => $activeRegion ? $activeRegion['name'] . ', ' . $country['name'] : $country['name'],
                'location_name' => $place['name'],
                'location_geoname_id' => $place['geonameId'],
                'location_latitude' => $place['latitude'],
                'location_longitude' => $place['longitude'],
                'location_admin_unit' => 1,
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
}
