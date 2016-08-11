<?php

namespace Rox\Member\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SearchFormFactory
{
    /**
     * @param FormFactoryInterface $formFactory
     *
     * @return FormInterface
     */
    public function __invoke(FormFactoryInterface $formFactory)
    {
        $form = $formFactory->createBuilder();

        $this->addHiddenFields($form);
        $this->addCheckboxes($form);
        $this->addSelects($form);
        $this->addButtons($form);

        $form->add('search', TextType::class, [
                    'attr' => [
                        'placeholder' => 'Where are you going?',
                    ],
                    'label' => false,
                ]);

        return $form->getForm();
    }

    protected function addHiddenFields(FormBuilderInterface $form)
    {
        $form
            ->add('search_geoname_id', HiddenType::class)
            ->add('search_latitude', HiddenType::class)
            ->add('search_longitude', HiddenType::class)
        ;
    }

    protected function addButtons(FormBuilderInterface $form)
    {
        $form->add('update_map', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-primary pull-xs-right',
                    ],
                ])
            ->add('advanced_options', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-sm btn-primary pull-xs-right',
                    ],
                ])
        ;
    }

    protected function addCheckboxes(FormBuilderInterface $form)
    {
        $form
            ->add('search_accommodation_anytime', CheckboxType::class, [
                    'label' => false,
                    'required' => false,
                    'data' => true,
                ])
            ->add('search_accommodation_dependonrequest', CheckboxType::class, [
                    'label' => false,
                    'required' => false,
                    'data' => true,
                ])
            ->add('search_accommodation_dontask', CheckboxType::class, [
                    'label' => false,
                    'required' => false,
                ])
        ;
    }

    protected function addSelects(FormBuilderInterface $form)
    {
        $form
            ->add('search_can_host', ChoiceType::class, [
                    'choices' => [
                        0 => '0',
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        10 => '10',
                        20 => '20',
                    ],
                    'attr' => [
                        'class' => 'form-control-label',
                    ],
                    'data' => '1',
                    'label' => 'hosts at least',
                ])
            ->add('search_distance', ChoiceType::class, [
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
                        'class' => 'form-control-label',
                    ],
                    'choices_as_values' => true,
                    'data' => '20',
                    'label' => 'in a radius of',
                ])
        ;
    }
}
