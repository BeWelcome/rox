<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TripType extends AbstractType
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
            ->add('summary', TextType::class, [
                'attr' => [
                    'placeholder' => 'Give a short summary of your trip',
                ],
                'label' => 'Summary',
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Let people know what your trip is all about',
                ],
                'label' => 'Description',
            ])
            ->add('countoftravellers', ChoiceType::class, [
                 'choices' => [
                    '' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                    '> 5' => 10,
                 ],
                 'label' => 'Travellers',
            ])
            ->add('additionalinfo', ChoiceType::class, [
                'choices' => [
                    '' => 0,
                    'TripsAdditionalInfoSingle' => 1,
                    'TripsAdditionalInfoCouple' => 2,
                    'TripsAdditionalInfoFriendsMixed' => 4,
                    'TripsAdditionalInfoFriendsSame' => 8,
                    'TripsAdditionalInfoFamily' => 16,
                ],
                'label' => 'Additional Info',
            ])
            ->add('subtrips', CollectionType::class, [
                'entry_type' => SubTripType::class,
                'allow_add'    => true,
                'allow_delete' => true,
            ])
            ->add('create', SubmitType::class);
    }
}
