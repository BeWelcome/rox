<?php

namespace Rox\Member\Controller;

use Rox\Member\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\Form\SubmitButton;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    public function searchAction(Request $request)
    {
        $form = $this->createForm(SearchFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // form was submitted and all inputs are valid now search for members and return return results to the page

            $results = $this->getResults($form);
        }

        $content = $this->render('@member/searchmembers.html.twig', [
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
     * @param array $data
     * @return array|string
     */
    protected function getResults(Form $form)
    {
        $data = $form->getData();

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

        if ($data['search_accommodation_dontask']) {
            $vars['search-accommodation'][] = 'dontask';
        }

        $vars['search-distance'] = $data['search_distance'];
        $vars['search-can-host'] = $data['search_can_host'];
        $vars['search-number-items'] = 10;
        $vars['search-sort-order'] = 6;

        $model = new \SearchModel();

        return $model->getResultsForLocation($vars);
    }
}
