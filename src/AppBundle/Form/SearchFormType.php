<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
                'label' => 'Specific words in profile',
                'required' => false,
            ])
        ;

        $this->addHiddenFields($formBuilder);
        $this->addCheckboxes($formBuilder);
        $this->addVariableSelects($formBuilder, $options);
        $this->addSelects($formBuilder);
        $this->addButtons($formBuilder);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'groups' => null,
            'languages' => null,
        ]);
    }

    protected function addVariableSelects(FormBuilderInterface $formBuilder, array $options)
    {
        $groups = [];
        if (null !== $options['groups']) {
            foreach ($options['groups'] as $group) {
                $groups[$group->getName()] = $group->getId();
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

    protected function addSelects(FormBuilderInterface $formBuilder)
    {
        $ageArray = [];
        for ($i = 18; $i <= 118; $i = $i + 2) {
            $ageArray[$i] = $i;
        }
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
                'label' => 'in a radius of',
            ])
            ->add('min_age', ChoiceType::class, [
                'choices' => $ageArray,
                'required' => false,
                'label' => 'minimum age',
            ])
            ->add('max_age', ChoiceType::class, [
                'choices' => $ageArray,
                'required' => false,
                'label' => 'maximum age',
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                    'other' => 'idonttell',
                ],
                'required' => false,
            ])
            ->add('order', ChoiceType::class, [
                'choices' => [
                    'Username ascending' => 2,
                    'Username descending' => 3,
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
            ])
            ->add('items', ChoiceType::class, [
                'choices' => [
                    5 => 5,
                    10 => 10,
                    20 => 20,
                    50 => 50,
                    100 => 100,
                ],
            ])
        ;
    }

    private function addHiddenFields(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('page', HiddenType::class)
            ->add('location_geoname_id', HiddenType::class)
            ->add('location_latitude', HiddenType::class)
            ->add('location_longitude', HiddenType::class);
    }

    private function addButtons(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('updateMap', SubmitType::class, [
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
        ;
    }
}
