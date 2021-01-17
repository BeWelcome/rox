<?php

namespace App\Form;

use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TripTypeB extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
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
            ->add('additionalinfo', ChoiceType::class, [
                'choices' => [
                    '' => TripAdditionalInfoType::NONE,
                    'trip.additional.info.single' => TripAdditionalInfoType::SINGLE,
                    'trip.additional.info.couple' => TripAdditionalInfoType::COUPLE,
                    'trip.additional.info.friends.mixed' => TripAdditionalInfoType::FRIENDS_MIXED,
                    'trip.additional.info.friends.same' => TripAdditionalInfoType::FRIENDS_SAME,
                    'trip.additional.info.family' => TripAdditionalInfoType::FAMILY,
                ],
                'label' => 'trip.additional.info',
                'required' => false,
            ])
            ->add('startdate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->add('enddate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('subtrips', CollectionType::class, [
                'entry_type' => SubtripTypeB::class,
                'entry_options' => [
                    'label' => false,
                ],
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//            'data_class' => TripB::class,
        ]);
    }
}
