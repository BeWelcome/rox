<?php

namespace Rox\Trip\Form;

use Rox\Core\Entity\SubTrip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubTripType extends AbstractType
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
            ->add('search', TextType::class, [
                'mapped' => false
            ])
            ->add('geonameid', HiddenType::class)
            ->add('latitude', HiddenType::class, [
                'mapped' => false
            ])
            ->add('longitude', HiddenType::class, [
                'mapped' => false
            ])
            ->add('arrival', DateType::class)
            ->add('departure', DateType::class)
            ->add('options', ChoiceType::class, [
                'choices' => [
                    '' => 0,
                    'TripsLocationOptionLookingForAHost' => 1,
                    'TripsLocationOptionLikeToMeetUp' => 2,
                ],
                'multiple' => true,
                'label' => 'Additional Info',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SubTrip::class,
        ));
    }
}
