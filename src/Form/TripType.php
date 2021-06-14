<?php

namespace App\Form;

use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TripType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('summary', TextType::class, [
                'attr' => [
                    'placeholder' => 'trip.summary.placeholder',
                ],
                'label' => 'trip.summary',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'trip.description.placeholder',
                ],
                'label' => 'trip.description',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('countoftravellers', ChoiceType::class, [
                'choices' => [
                    '' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                    '> 5' => 6,
                ],
                'label' => 'trip.travellers',
            ])
            ->add('invitationradius', ChoiceType::class, [
                'choices' => [
                    'exact' => 0,
                    '5km' => 5,
                    '10km' => 10,
                    '20km' => 20,
                    '50km' => 50,
                    '100km' => 100,
                    '200km' => 200,
                ],
                'label' => 'trip.invitation.radius',
            ])
            ->add('additionalinfo', ChoiceType::class, [
                'choices' => [
                    '' => TripAdditionalInfoType::NONE,
                    'trip.additional.info.couple' => TripAdditionalInfoType::COUPLE,
                    'trip.additional.info.friends.mixed' => TripAdditionalInfoType::FRIENDS_MIXED,
                    'trip.additional.info.friends.same' => TripAdditionalInfoType::FRIENDS_SAME,
                    'trip.additional.info.family' => TripAdditionalInfoType::FAMILY,
                ],
                'label' => 'trip.additional.info',
                'required' => false,
            ])
            ->add('subtrips', CollectionType::class, [
                'entry_type' => SubtripType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'required' => true,
                'allow_add' => true,
                'by_reference' => false,
                'prototype' => true,
//                'error_bubbling' => false,
                'label' => false,
                'attr' => [
                    'class' => 'form-collection',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
