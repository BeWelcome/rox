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
        $searchTerm = $request->query->get('term', '');

        $this->logSearchInfo(__METHOD__, $searchTerm);

        $result = $model->getSuggestionsForPlacesExact($searchTerm);
        $response->setData($result);

        return $response;
    }

    /**
     * @Route("/suggest/locations/places", name="suggest_locations")
     */
    public function suggestPlaces(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerm = $request->query->get('term', '');

        $this->logSearchInfo(__METHOD__, $searchTerm);

        $result = $model->getSuggestionsForPlaces($searchTerm);
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
        $searchTerm = $request->query->get('term', '');

        $this->logSearchInfo(__METHOD__, $searchTerm);

        $result = $model->getSuggestionsForLocations($searchTerm);
        $response->setData($result);

        return $response;
    }

    private function logSearchInfo(string $function, string $searchTerm)
    {
        $this->logger->alert($function, ['locale' => $this->translator->getLocale(), 'searchTerm' => $searchTerm]);
    }
}
