<?php

namespace App\Form;

use App\Form\CustomDataClass\SearchFormRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('location', TextType::class, [
                'attr' => [
                    'placeholder' => 'landing.whereyougo',
                ],
                'label' => false,
                'error_bubbling' => true,
                'translation_domain' => 'messages',
            ])
            ->setMethod('GET')
            ->add('keywords', TextType::class, [
                'label' => 'texttofind',
                'required' => false,
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'onPostSetData']
            )
        ;

        $this->addHiddenFields($formBuilder);
        $this->addCheckboxes($formBuilder);
        $this->addVariableSelects($formBuilder, $options);
        $this->addAgeAndGenderSelects($formBuilder);
        $this->addSelects($formBuilder);
        $this->addButtons($formBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'groups' => null,
            'languages' => null,
            'validation_groups' => [
                SearchFormRequest::class,
                'determineValidationGroups',
            ],
            'translation_domain' => 'messages',
            'allow_extra_fields' => true,
            'error_mapping' => [
                '.' => 'location',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    /**
     * Add 'see map' option in case the map shows result from a zoom/pan operation or
     * an empty search location (\todo).
     *
     * @param FormEvent $event
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     */
    public function onPostSetData(FormEvent $event)
    {
        $data = $event->getData();
        $choices = [
            'exact' => 0,
            '5km (~3mi)' => 5,
            '10km (~6mi)' => 10,
            '15km (~10mi)' => 15,
            '20km (~15mi)' => 20,
            '50km (~31mi)' => 50,
            '100km (~63mi)' => 100,
            '200km (~128mi)' => 200,
        ];
        $showOnMap = (bool) ($data->showOnMap);
        if (true === $showOnMap) {
            $choices = ['search.see_map' => -1] + $choices;
        }
        $form = $event->getForm();
        $form->add('distance', ChoiceType::class, [
            'choices' => $choices,
            'attr' => [
                'class' => 'select2-inline',
                'data-minimum-results-for-search' => '-1',
            ],
            'label' => 'label.radius',
            'label_attr' => [
                'class' => 'mr-1 sr-only',
            ],
            'translation_domain' => false,
        ]);
    }

    protected function addVariableSelects(FormBuilderInterface $formBuilder, array $options)
    {
        $groups = [];
        if (null !== $options['groups']) {
            foreach ($options['groups'] as $group) {
                if ($group->getApproved()) {
                    $groups[$group->getName()] = $group->getId();
                }
            }
        }
        $languages = [];
        if (null !== $options['languages']) {
            foreach ($options['languages'] as $language) {
                $languages['lang_' . $language->getShortCode()] = $language->getId();
            }
        }
        $formBuilder
            ->add('groups', ChoiceType::class, [
                'choices' => $groups,
                'choice_translation_domain' => false,
                'label' => 'groups',
                'attr' => [
                    'class' => 'select2',
                ],
                'multiple' => true,
                'required' => false,
            ])
            ->add('languages', ChoiceType::class, [
                'choices' => $languages,
                'label' => 'languages',
                'attr' => [
                    'class' => 'select2',
                ],
                'multiple' => true,
                'required' => false,
            ]);
    }

    protected function addAgeAndGenderSelects(FormBuilderInterface $formBuilder)
    {
        $ageArray = [];
        for ($i = 18; $i <= 118; $i = $i + 2) {
            $ageArray[$i] = $i;
        }
        $formBuilder
            ->add('min_age', ChoiceType::class, [
                'choices' => $ageArray,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'required' => false,
                'label' => 'findpeopleminimumage',
                'translation_domain' => 'messages',
            ])
            ->add('max_age', ChoiceType::class, [
                'choices' => $ageArray,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'required' => false,
                'label' => 'findpeoplemaximumage',
                'translation_domain' => 'messages',
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                    'other' => 'idonttell',
                ],
                'label' => 'gender',
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'required' => false,
                'translation_domain' => 'messages',
            ]);
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
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2-inline',
                    'data-minimum-results-for-search' => '-1',
                ],
                'label' => 'searchcanhostatleast',
                'label_attr' => [
                    'class' => 'mx-1 sr-only',
                ],
                'translation_domain' => 'messages',
            ])
            ->add('order', ChoiceType::class, [
                'label' => 'label.order',
                'choices' => [
                    'searchorderusernameasc' => 2,
                    'searchorderusernamedesc' => 3,
                    'searchorderaccommodationasc' => 6,
                    'searchorderaccommodationdesc' => 7,
                    'searchorderdistanceasc' => 14,
                    'searchorderdistancedesc' => 15,
                    'searchorderloginasc' => 8,
                    'searchorderlogindesc' => 9,
                    'searchordermembershipasc' => 10,
                    'searchordermembershipdesc' => 11,
                    'searchordercommentsasc' => 12,
                    'searchordercommentsdesc' => 13,
                ],
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'translation_domain' => 'messages',
            ])
            ->add('items', ChoiceType::class, [
                'label' => 'label.items',
                'choices' => [
                    5 => 5,
                    10 => 10,
                    20 => 20,
                    50 => 50,
                    100 => 100,
                ],
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'translation_domain' => 'messages',
            ])
        ;
    }

    private function addHiddenFields(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('location_geoname_id', HiddenType::class)
            ->add('location_latitude', HiddenType::class)
            ->add('location_longitude', HiddenType::class)
            ->add('showOnMap', HiddenType::class)
            ->add('ne_latitude', HiddenType::class)
            ->add('ne_longitude', HiddenType::class)
            ->add('sw_latitude', HiddenType::class)
            ->add('sw_longitude', HiddenType::class);
    }

    private function addButtons(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('updateMap', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
                'label' => 'search.find.members',
            ]);
    }

    private function addCheckboxes(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('accommodation_anytime', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('accommodation_dependonrequest', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('accommodation_neverask', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('offerdinner', CheckboxType::class, [
                'label' => 'typicoffer_dinner',
                'required' => false,
            ])
            ->add('offertour', CheckboxType::class, [
                'label' => 'typicoffer_guidedtour',
                'required' => false,
            ])
            ->add('accessible', CheckboxType::class, [
                'label' => 'typicoffer_canhostwheelchair',
                'required' => false,
            ])
            ->add('inactive', CheckboxType::class, [
                'label' => 'searchincludeinactive',
                'required' => false,
            ])
            ->add('showmap', CheckboxType::class, [
                'label' => 'search.show.map',
                'required' => false,
            ])
            ->add('showadvanced', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'd-none',
                ],
            ])
        ;
    }
}
