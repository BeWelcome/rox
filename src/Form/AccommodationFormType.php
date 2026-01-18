<?php

namespace App\Form;

use App\Doctrine\AccommodationType;
use App\Doctrine\HostRestrictionsType;
use App\Doctrine\StandardOffersType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class AccommodationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('max_guests', IntegerType::class, [
                'label' => 'label.profile.max_guests',
                'help' => 'help.profile.max_guests',
                'attr' => [
                    'class' => 'o-input',
                    'min' => 1,
                    'max' => 20,
                ],
            ])
            ->add('accommodation', ChoiceType::class, [
                'label' => 'label.accommodation',
                'expanded' => true,
                'multiple' => false,
                'help' => 'help.accommodation',
                'choices' => [
                    'accommodation.no' => AccommodationType::NO,
                    'accommodation.yes' => AccommodationType::YES,
                ],
                'required' => false,
                'constraints' => [
                    new NotNull(message: 'error.accommodation'),
                ],
            ])
            ->add('hosting_interest', RangeType::class, [
                'label' => 'label.hosting_interest',
                'help' => 'help.hosting_interest',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'max' => 10,
                ],
            ])
            ->add('restrictions', ChoiceType::class, [
                'label' => 'label.restrictions',
                'expanded' => true,
                'multiple' => true,
                'help' => 'help.restrictions',
                'choices' => [
                    'restriction.no_alcohol' => HostRestrictionsType::NO_ALCOHOL,
                    'restriction.no_smoking' => HostRestrictionsType::NO_SMOKING,
                    'restriction.no_drugs' => HostRestrictionsType::NO_DRUGS,
                ],
            ])
            ->add('offers', ChoiceType::class, [
                'label' => 'label.offers',
                'expanded' => true,
                'multiple' => true,
                'help' => 'help.offers',
                'choices' => [
                    'offer.dinner' => StandardOffersType::DINNER,
                    'offer.guided_tour' => StandardOffersType::GUIDED_TOUR,
                ],
            ])
            ->add('wheelchair_accessible', CheckboxType::class, [
                'required' => false,
                'label' => 'label.profile.wheelchair_accessible',
                'help' => 'help.profile.wheelchair_accessible',
            ])
            ->add('length_of_stay', CkEditorType::class, [
                'label' => 'label.profile.length_of_stay',
                'help' => 'help.profile.length_of_stay',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('i_live_with', CkEditorType::class, [
                'label' => 'label.profile.i_live_with',
                'help' => 'help.profile.i_live_with',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('please_bring', CkEditorType::class, [
                'label' => 'label.profile.please_bring',
                'help' => 'help.profile.please_bring',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('where_you_sleep', CkEditorType::class, [
                'label' => 'label.profile.where_you_sleep',
                'help' => 'help.profile.where_you_sleep',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => true,
            ])
            ->add('offer_guests', CkEditorType::class, [
                'label' => 'label.profile.offer_guests',
                'help' => 'help.profile.offer_guests',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('additional_info', CkEditorType::class, [
                'label' => 'label.profile.additional_info',
                'help' => 'help.profile.additional_info',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('getting_there', CkEditorType::class, [
                'label' => 'label.profile.getting_there',
                'help' => 'help.profile.getting_there',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('house_rules', CkEditorType::class, [
                'label' => 'label.profile.house_rules',
                'help' => 'help.profile.house_rules',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}
