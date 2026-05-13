<?php

namespace App\Controller;

use App\Model\SuggestLocationModel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuggestLocationController extends AbstractController
{
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @Route("/suggest/locations/places/exact", name="suggest_locations_exact")
     */
    public function suggestExactPlaces(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerms = $this->splitElements($request->query->get('term', ''));

        $this->logSearchInfo(__METHOD__, $searchTerms);

        $result = $model->getSuggestionsForPlacesExact($searchTerms);
        $response->setData($result);

        return $response;
    }

    /**
     * @Route("/suggest/locations/places", name="suggest_locations")
     */
    public function suggestPlaces(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerms = $this->splitElements($request->query->get('term', ''));

        $this->logSearchInfo(__METHOD__, $searchTerms);

        $result = $model->getSuggestionsForPlaces($searchTerms);
        $response->setData($result);

        return $response;
    }

    /**
     * @Route("/suggest/locations/all", name="suggest_locations_all")
     *
     * This differs from suggestPlaces in that way that it also allows to find regions and countries (used on the
     * search locations page).
     */
    public function suggestLocations(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerms = $this->splitElements($request->query->get('term', ''));

        $this->logSearchInfo(__METHOD__, $searchTerms);

        $result = $model->getSuggestionsForLocations($searchTerms);
        $response->setData($result);

        return $response;
    }

    private function logSearchInfo(string $function, array $searchTerms)
    {
        $this->logger->alert($function, ['locale' => $this->translator->getLocale(), 'searchTerms' => $searchTerms]);
    }

    private function splitElements(string $searchTerm): array
    {
        return array_filter(array_map('trim', explode(',', $searchTerm)), 'strlen');
    }
}
