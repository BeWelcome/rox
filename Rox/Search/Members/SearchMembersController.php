<?php

namespace Rox\Search\Members;

use Rox\Framework\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotIdenticalTo;
use Symfony\Component\Validator\Constraints\Type;

class SearchMembersController extends Controller
{

    public function __construct()
    {
        $this->setModel(new \SearchModel());
    }

    public function searchAction(Request $request) {
        // We need a new page
        $page = new SearchMembersPage($this->getRouting());

        // Setup the form used inside the template
        // \todo move to a separate form class
        $form = $this->getFormFactory()->createBuilder()
            ->add('search_geoname_id',  'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('search_latitude', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('search_longitude', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('search', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'attr' => [
                'placeholder' => 'Where are you going?'
                ],
                'label' => false
            ])
            ->add('search_can_host', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                    'choices' => [
                        '0' => 0,
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                        '4' => 4,
                        '5' => 5,
                        '10' => 10,
                        '20' => 20
                    ],
                    'attr' => [
                        'class' => 'form-control-label'
                    ],
                    'choices_as_values' => false,
                    'data' => '1',
                    'label' => 'hosts at least'
                ]
            )
            ->add('search_distance', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                    'choices' => [
                        'exact' => 0,
                        '5km / 3mi' => 5,
                        '10km / 6mi' => 10,
                        '20km / 15mi' => 20,
                        '50km / 31mi' => 50,
                        '100km / 63mi' => 100,
                        '200km / 128mi' => 200,
                    ],
                    'attr' => [
                        'class' => 'form-control-label'
                    ],
                    'choices_as_values' => true,
                    'data' => '20',
                    'label' => 'in a radius of'
                ]
            )
            ->add('search_accommodation_anytime', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => false,
                'required' => false,
                'data' => true
            ])
            ->add('search_accommodation_dependonrequest', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => false,
                'required' => false,
                'data' => true
            ])
            ->add('search_accommodation_dontask', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => false,
                'required' => false
            ])
            ->add('update_map', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'attr' => [
                    'class' => 'btn btn-primary pull-xs-right'
                ]
            ])
            ->add('advanced_options', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'attr' => [
                    'class' => 'btn btn-sm btn-primary pull-xs-right'
                ]
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // form was submitted and all inputs are valid now search for members and return return results to the page
            if ($form->get('advanced_options')->isClicked()) {
                $page->addParameters([
                    'results' => 'Advanced options? Really?'
                ]);
            } else {
                $data = $form->getData();
                $vars = [];
                $vars['location'] = $data['search'];
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
                $result = $this->getModel()->getResultsForLocation($vars);
                $page->addParameters(['results' => $result]);
            }
        }
        $page->initializeFormComponent(true);
        $page->addForm($form);
        return new Response($page->render());
    }
}