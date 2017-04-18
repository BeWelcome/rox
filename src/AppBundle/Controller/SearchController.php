<?php

namespace AppBundle\Controller;

use AppBundle\Form\SearchFormType;
use EnvironmentExplorer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    /**
     * @Route("/search/members", name="search_members")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $results = false;
        // Check if request contains a standard search form or one of the specialized search form
        // if the latter turn them into a standard form (add missing default fields).
        if ($request->request->has('search_home_location_form')) {
            $request = $this->updateRequestSearchFormData($request, 'search_home_location_form');
        }
        if ($request->request->has('search_goto_location_form')) {
            $request = $this->updateRequestSearchFormData($request, 'search_goto_location_form');
        }

        $form = $this->createForm(SearchFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $results = $this->getResults($form->getData());
        }

        $content = $this->render(':search:searchmembers.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
            'location' => [
                'name' => null,
                'geonameid' => 0,
                'latitude' => null,
                'longitude' => null,
            ],
        ]);

        return new Response($content);
    }

    /**
     * @param Request $request
     * @param $formName
     *
     * @return Request
     */
    private function updateRequestSearchFormData(Request $request, $formName)
    {
        $data = $request->request->get($formName);
        $data['search_accommodation_anytime'] = true;
        $data['search_accommodation_dependonrequest'] = true;
        $data['search_accommodation_neverask'] = true;
        $data['search_can_host'] = 1;
        $data['search_distance'] = 5;

        $request->request->remove($formName);
        $request->request->add(['search_form' => $data]);

        return $request;
    }

    /**
     * @param array $data
     *
     * @return array|string
     */
    private function getResults($data)
    {
        $vars = [];
        $vars['search-location'] = $data['search'];
        $vars['location-geoname-id'] = $data['search_geoname_id'];
        $vars['location-latitude'] = $data['search_latitude'];
        $vars['location-longitude'] = $data['search_longitude'];
        $vars['search-accommodation'] = [];

        if ($data['search_accommodation_anytime']) {
            $vars['search-accommodation'][] = 'anytime';
        }

        if ($data['search_accommodation_dependonrequest']) {
            $vars['search-accommodation'][] = 'dependonrequest';
        }

        if ($data['search_accommodation_neverask']) {
            $vars['search-accommodation'][] = 'neverask';
        }

        $vars['search-distance'] = $data['search_distance'];
        $vars['search-can-host'] = $data['search_can_host'];
        $vars['search-number-items'] = 10;
        $vars['search-sort-order'] = 6;

        // make sure everything's setup for the old code used below
        $container = $this->get('service_container');
        $environmentExplorer = new EnvironmentExplorer();
        $environmentExplorer->initializeGlobalState(
            $container->getParameter('database_host'),
            $container->getParameter('database_name'),
            $container->getParameter('database_user'),
            $container->getParameter('database_password')
        );

        $model = new \SearchModel();

        return $model->getResultsForLocation($vars);
    }
}
