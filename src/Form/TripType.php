<?php

namespace App\Form;

use App\Doctrine\TripAdditionalInfoType;
use App\Dto\TripDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('summary', TextType::class, [
                'attr' => [
                    'placeholder' => 'trip.summary.placeholder',
                ],
                'label' => 'trip.summary',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'trip.description.placeholder',
                ],
                'label' => 'trip.description',
                'required' => false,
            ])
            ->add('countOfTravellers', ChoiceType::class, [
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
                'choice_translation_domain' => false,
            ])
            ->add('invitationRadius', ChoiceType::class, [
                'choices' => [
                    'exact' => 0,
                    '5km' => 5,
                    '10km' => 10,
                    '20km' => 20,
                    '50km' => 50,
                    '100km' => 100,
                ],
                'label' => 'trip.invitation.radius',
                'choice_translation_domain' => false,
            ])
            ->add('additionalInfo', ChoiceType::class, [
                'choices' => [
                    'trip.additional.info.none' => TripAdditionalInfoType::NONE,
                    'trip.additional.info.couple' => TripAdditionalInfoType::COUPLE,
                    'trip.additional.info.friends.mixed' => TripAdditionalInfoType::FRIENDS_MIXED,
                    'trip.additional.info.friends.same' => TripAdditionalInfoType::FRIENDS_SAME,
                    'trip.additional.info.family' => TripAdditionalInfoType::FAMILY,
                ],
                'label' => 'trip.additional.info',
                'required' => true,
            ])
            ->add('legs', CollectionType::class, [
                'entry_type' => LegType::class,
                'entry_options' => [
                    'label' => false,
                    'error_bubbling' => false,
                ],
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-collection',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TripDto::class,
        ]);
    }
}
