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
     * @Route("/suggest/locations/places/{ranker}", name="suggest_locations")
     */
    public function suggestPlaces(Request $request, string $ranker, SuggestLocationModel $model): JsonResponse
    {
        $response = new JsonResponse();
        $searchTerm = $request->query->get('term', '');
        switch ($ranker) {
            case 'ranker1':
                $expr = 'sum((min_hit_pos==1)*1000+exact_hit*500)';
                break;
            case 'ranker2':
                $expr = 'sum((min_hit_pos==1)*1000+exact_hit*500)+membercount';
                break;
            case 'ranker3':
                $expr = 'sum((min_hit_pos==1)*1000+exact_hit*500)+population/1000';
                break;
        }
        $result = $model->getSuggestionsForPlaces($searchTerm, $expr);
        $response->setData($result);

        return $response;
    }
}
