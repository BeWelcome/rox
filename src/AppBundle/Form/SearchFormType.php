<?php

namespace AppBundle\Form;

use AppBundle\Form\CustomDataClass\SearchFormRequest;
use AppBundle\Form\CustomDataClass\SearchHomeLocationRequest;
use AppBundle\Form\CustomDataClass\WhereDoYouWantToGoRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class SearchFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('location', TextType::class, [
            'attr' => [
                'placeholder' => 'Where are you going?',
            ],
            'label' => false,
        ])
            ->setMethod('GET')
            ->setAction('/search/members');

        $this->addHiddenFields($formBuilder);
        $this->addCheckboxes($formBuilder);
        $this->addSelects($formBuilder);
        $this->addButtons($formBuilder);
    }

    protected function addSelects(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('can_host', ChoiceType::class, [
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
            ->add('distance', ChoiceType::class, [
                'choices' => [
                    'exact' => 0,
                    '5km (~3mi)' => 5,
                    '10km (~6mi)' => 10,
                    '20km (~15mi)' => 20,
                    '50km (~31mi)' => 50,
                    '100km (~63mi)' => 100,
                    '200km (~128mi)' => 200,
                ],
                'attr' => [
                    'class' => 'form-control-label',
                ],
                'data' => '20',
                'label' => 'in a radius of',
            ]);
    }

    private function addHiddenFields(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('page', HiddenType::class)
            ->add('geoname_id', HiddenType::class)
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class);
    }

    private function addButtons(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('update_map', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ]);
    }

    private function addCheckboxes(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('accommodation_anytime', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'data' => true,
            ])
            ->add('accommodation_dependonrequest', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'data' => true,
            ])
            ->add('accommodation_neverask', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'data' => false,
            ]);
    }
}
