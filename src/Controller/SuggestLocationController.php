<?php

namespace App\Controller;

use App\Model\SuggestLocationModel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuggestLocationController extends AbstractController
{
    public function __construct(private readonly LoggerInterface $logger, private readonly TranslatorInterface $translator)
    {
    }

    #[Route(path: '/suggest/locations/places/exact', name: 'suggest_locations_exact')]
    public function suggestExactPlaces(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerms = $this->splitElements($request->query->get('term', ''));

        $this->logSearchInfo(__METHOD__, $searchTerms);

        $result = [] === $searchTerms ? ['locations' => []] : $model->getSuggestionsForPlacesExact($searchTerms);
        $response->setData($result);

        return $response;
    }

    #[Route(path: '/suggest/locations/places', name: 'suggest_locations')]
    public function suggestPlaces(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerms = $this->splitElements($request->query->get('term', ''));

        $this->logSearchInfo(__METHOD__, $searchTerms);

        $result = [] === $searchTerms ? ['locations' => []] : $model->getSuggestionsForPlaces($searchTerms);
        $response->setData($result);

        return $response;
    }

    #[Route(path: '/suggest/locations/all', name: 'suggest_locations_all')] // This differs from suggestPlaces in that way that it also allows to find regions and countries (used on the
    public function suggestLocations(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerms = $this->splitElements($request->query->get('term', ''));

        $this->logSearchInfo(__METHOD__, $searchTerms);

        $result = [] === $searchTerms ? ['locations' => []] : $model->getSuggestionsForLocations($searchTerms);
        $response->setData($result);

        return $response;
    }

    private function logSearchInfo(string $function, array $searchTerms): void
    {
        // debug: at alert, every search wrote the request's buffered records (production logs on errors and up)
        $this->logger->debug($function, ['locale' => $this->translator->getLocale(), 'searchTerms' => $searchTerms]);
    }

    /**
     * The model searches the place in the first term, so the terms are reindexed: ', Berlin' gives ['Berlin'].
     */
    private function splitElements(string $searchTerm): array
    {
        return array_values(array_filter(array_map(trim(...), explode(',', $searchTerm)), strlen(...)));
    }
}
