<?php

namespace App\Controller;

use App\Model\SuggestLocationModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuggestLocationController extends AbstractController
{
    /**
     * @Route("/suggest/location", name="suggest_location")
     */
    public function index(): Response
    {
        return $this->render('suggest_location/index.html.twig');
    }

    /**
     * @Route("/suggest/locations/all", name="suggest_locations")
     */
    public function suggestPlaces(Request $request, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerm = $request->query->get('term', '');
        $result = $model->getSuggestionsForPlaces($searchTerm);
        $response->setData($result);

        return $response;
    }
}
