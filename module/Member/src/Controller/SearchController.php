<?php

namespace Rox\Member\Controller;

use Rox\Member\Form\SearchFormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class SearchController
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormInterface
     */
    protected $form;

    public function __construct(EngineInterface $engine, FormFactoryInterface $formFactory)
    {
        $this->engine = $engine;
        $this->formFactory = $formFactory;
    }

    public function searchAction(Request $request)
    {
        $factory = new SearchFormFactory();

        $this->form = $factory->__invoke($this->formFactory);

        $form = $this->form;

        $form->handleRequest($request);

        $results = [
            'members' => [],
            'map' => [],
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            // form was submitted and all inputs are valid now search for members and return return results to the page

            $results = $this->getResults();
        }

        $content = $this->engine->render('@member/searchmembers.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
            'location' => [
                'name' => null,
                'geonameid' => 0,
                'latitude' => null,
                'longitude' => null,
            ],
//            'accommodation' => [
//                'yes' => 'checked',
//                'maybe' => '',
//                'no' => 'checked'
//            ],
//            'canhost' => [
//                'values' => [
//                    0 => '0',
//                    1 => '1',
//                    2 => '2',
//                    3 => '3',
//                    4 => '4',
//                    5 => '5',
//                    10 => '10',
//                    20 => '20'
//                ],
//                'value' => 4
//            ],
//            'radius' => [
//                'values' => [
//                    5 => '5km / 3mi',
//                    10 => '10km / 6mi',
//                    25 => '20km / 15mi',
//                    50 => '50km / 31mi',
//                    100 => '100km / 63mi',
//                ],
//                'value' => 25
//            ]
        ]);

        return new Response($content);
    }

    protected function getResults()
    {
        /** @var SubmitButton $advanced */
        $advanced = $this->form->get('advanced_options');

        if ($advanced->isClicked()) {
            return 'Advanced options? Really?';
        }

        $data = $this->form->getData();

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

        //                return $vars;
        //                $result = $this->getModel()->getResultsForLocation($vars);
        //                $page->addParameters(['results' => $result]);

        $model = new \SearchModel();

        return $model->getResultsForLocation($vars);
    }
}
