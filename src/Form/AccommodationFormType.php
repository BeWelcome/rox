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
                'label' => 'profile.max.guests',
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
                'label' => 'hosting.interest',
                'help' => 'help.hosting.interest',
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
                    'restriction.no.alcohol' => HostRestrictionsType::NO_ALCOHOL,
                    'restriction.no.smoking' => HostRestrictionsType::NO_SMOKING,
                    'restriction.no.drugs' => HostRestrictionsType::NO_DRUGS,
                ],
            ])
            ->add('offers', ChoiceType::class, [
                'label' => 'label.offers',
                'expanded' => true,
                'multiple' => true,
                'help' => 'help.offers',
                'choices' => [
                    'offer.dinner' => StandardOffersType::DINNER,
                    'offer.guided.tour' => StandardOffersType::GUIDED_TOUR,
                ],
            ])
            ->add('wheelchair_accessible', CheckboxType::class, [
                'required' => false,
                'label' => 'profile.wheelchair.accessible',
                'help' => 'help.profile.wheelchair.accessible',
            ])
            ->add('length_of_stay', CkEditorType::class, [
                'label' => 'profile.max.length.of.stay',
                'help' => 'help.profile.length.of.stay',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('i_live_with', CkEditorType::class, [
                'label' => 'profile.i.live.with',
                'help' => 'help.profile.i.live.with',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('please_bring', CkEditorType::class, [
                'label' => 'profile.please.bring',
                'help' => 'help.profile.please.bring',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('where_you_sleep', CkEditorType::class, [
                'label' => 'profile.where.you.sleep',
                'help' => 'help.profile.where.you.sleep',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('offer_guests', CkEditorType::class, [
                'label' => 'profile.offer.guests',
                'help' => 'help.profile.offer.guests',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('additional_info', CkEditorType::class, [
                'label' => 'profile.additional.information.for.guests',
                'help' => 'help.profile.additional.information.for.guests',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('getting_there', CkEditorType::class, [
                'label' => 'profile.getting.there',
                'help' => 'help.profile.getting.there',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('house_rules', CkEditorType::class, [
                'label' => 'profile.house.rules',
                'help' => 'help.profile.house.rules',
                'async' => true,
                'required' => false,
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'image_upload' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}
