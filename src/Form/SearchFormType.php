<?php

namespace App\Form;

use App\Form\CustomDataClass\SearchFormRequest;
use Symfony\Component\Form\AbstractType;
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
                    'placeholder' => 'Where are you going?',
                ],
                'label' => false,
            ])
            ->setMethod('GET')
            ->add('keywords', TextType::class, [
                'label' => 'TextToFind',
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
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
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
                $languages[$language->getEnglishName()] = $language->getId();
            }
        }
        $formBuilder
            ->add('groups', ChoiceType::class, [
                'choices' => $groups,
                'attr' => [
                    'class' => 'select2',
                ],
                'multiple' => true,
                'required' => false,
            ])
            ->add('languages', ChoiceType::class, [
                'choices' => $languages,
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
                'label' => 'Minimum age',
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
                'label' => 'Maximum age',
                'translation_domain' => 'messages',
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                    'other' => 'idonttell',
                ],
                'choice_translation_domain' => false,
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
                'label' => 'search.hosts_at_least',
                'label_attr' => [
                    'class' => 'mx-1 sr-only',
                ],
                'translation_domain' => 'messages',
            ])
            ->add('order', ChoiceType::class, [
                'choices' => [
                    'search.username.ascending' => 2,
                    'search.username.descending' => 3,
                    'Accommodation (Yes, Maybe, No)' => 6,
                    'Accommodation (No, Maybe, Yes)' => 7,
                    'Distance ascending' => 14,
                    'Distance descending' => 15,
                    'Last login (oldest first)' => 8,
                    'Last login (latest first)' => 9,
                    'Member since (older member first)' => 10,
                    'Member since (newer member first)' => 11,
                    'Number of comments ascending' => 12,
                    'Number of comments descending' => 13,
                ],
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'translation_domain' => 'messages',
            ])
            ->add('items', ChoiceType::class, [
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
            ->add('page', HiddenType::class)
            ->add('location_geoname_id', HiddenType::class, [
//                'error_mapping' => 'location',
            ])
            ->add('location_latitude', HiddenType::class, [
//                'error_mapping' => 'location',
            ])
            ->add('location_longitude', HiddenType::class, [
//                'error_mapping' => 'location',
            ])
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
                'label' => 'TypicOffer_dinner',
                'required' => false,
            ])
            ->add('offertour', CheckboxType::class, [
                'label' => 'TypicOffer_guidedtour',
                'required' => false,
            ])
            ->add('accessible', CheckboxType::class, [
                'label' => 'TypicOffer_CanHostWheelChair',
                'required' => false,
            ])
            ->add('inactive', CheckboxType::class, [
                'label' => 'SearchIncludeInactive',
                'required' => false,
            ])
            ->add('showmap', CheckboxType::class, [
                'label' => 'search.show.map',
                'required' => false,
            ])
        ;
    }

    /*
     *
     */
    /**
     * Add 'see map' option in case the map shows result from a zoom/pan operation or
     * an empty search location (\todo)
     *
     * @param FormEvent $event
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
        $showOnMap = boolval($data->showOnMap);
        if (true === $showOnMap) {
            $choices = ['see map' => -1] + $choices;
        }
        $form = $event->getForm();
        $form->add('distance', ChoiceType::class, [
            'choices' => $choices,
            'choice_translation_domain' => false,
            'attr' => [
                'class' => 'select2-inline',
                'data-minimum-results-for-search' => '-1',
            ],
            'label' => 'in a radius of',
            'label_attr' => [
                'class' => 'mr-1 sr-only',
            ],
            'translation_domain' => 'messages',
        ]);
    }
}
