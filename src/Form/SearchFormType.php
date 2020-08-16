<?php

namespace App\Form;

use App\Form\CustomDataClass\SearchFormRequest;
use SearchModel;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('location', TextType::class, [
                'label' => 'landing.whereyougo',
                'attr' => [
                    'placeholder' => 'landing.whereyougo',
                ],
                'error_bubbling' => true,
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
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     */
    public function onPostSetData(FormEvent $event)
    {
        $data = $event->getData();
        $choices = [
            'search.radius.exact' => 0,
            'search.radius.5km' => 5,
            'search.radius.10km' => 10,
            'search.radius.15km' => 15,
            'search.radius.20km' => 20,
            'search.radius.50km' => 50,
            'search.radius.100km' => 100,
            'search.radius.200km' => 200,
        ];
        $showOnMap = (bool) ($data->showOnMap);
        if (true === $showOnMap) {
            $choices = ['search.see_map' => -1] + $choices;
        }
        $form = $event->getForm();
        $form->add('distance', ChoiceType::class, [
            'choices' => $choices,
            'label' => 'label.radius',
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
                'label' => 'searchcanhostatleast',
                'translation_domain' => 'messages',
            ])
            ->add('last_login', ChoiceType::class, [
                'label' => 'search.filter.last.login',
                'choices' => [
                    'search.filter.last.login.1month' => 1,
                    'search.filter.last.login.2months' => 2,
                    'search.filter.last.login.3months' => 3,
                    'search.filter.last.login.6months' => 6,
                    'search.filter.last.login.year' => 12,
                    'search.filter.last.login.all' => 2400,
                ],
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'translation_domain' => 'messages',
            ])
            ->add('order', ChoiceType::class, [
                'label' => 'label.order',
                'choices' => [
                    'search.order.accommodation' => 6,
                    'search.order.distance' => 14,
                    'search.order.login' => 8,
                    'search.order.comments' => 12,
                    'search.order.membership' => 10,
                    'search.order.username' => 2,
                ],
                'attr' => [
                    'class' => 'select2',
                    'data-minimum-results-for-search' => '-1',
                ],
                'translation_domain' => 'messages',
            ])
            ->add('direction', ChoiceType::class, [
                'label' => 'label.direction',
                'choices' => [
                    'search.direction.descending' => SearchModel::DIRECTION_DESCENDING,
                    'search.direction.ascending' => SearchModel::DIRECTION_ASCENDING,
                ],
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
                'label' => 'search.accommodation.yes',
                'required' => false,
            ])
            ->add('accommodation_neverask', CheckboxType::class, [
                'label' => 'search.accommodation.no',
                'required' => false,
            ])
            ->add('offerdinner', CheckboxType::class, [
                'label' => 'search.offer.dinner',
                'required' => false,
            ])
            ->add('offertour', CheckboxType::class, [
                'label' => 'search.offer.guided.tour',
                'required' => false,
            ])
            ->add('accessible', CheckboxType::class, [
                'label' => 'search.offer.accessible',
                'required' => false,
            ])
            ->add('profile_picture', CheckboxType::class, [
                'label' => 'search.has.profile.picture',
                'required' => false,
            ])
            ->add('about_me', CheckboxType::class, [
                'label' => 'search.has.about.me',
                'required' => false,
            ])
            ->add('no_smoking', CheckboxType::class, [
                'label' => 'search.restriction.no.smoking',
                'required' => false,
            ])
            ->add('no_alcohol', CheckboxType::class, [
                'label' => 'search.restriction.no.alcohol',
                'required' => false,
            ])
            ->add('no_drugs', CheckboxType::class, [
                'label' => 'search.restriction.no.drugs',
                'required' => false,
            ])
            ->add('has_comments', CheckboxType::class, [
                'label' => 'search.filter.has.comments',
                'required' => false,
            ])
            ->add('show_map', CheckboxType::class, [
                'label' => 'search.show.map',
                'required' => false,
            ])
            ->add('show_options', CheckboxType::class, [
                'label' => 'search.show.options',
                'required' => false,
            ])
        ;
    }
}
