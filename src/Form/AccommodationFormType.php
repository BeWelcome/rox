<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;

class AccommodationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('max_guests', NumberType::class, [
                'label' => 'label.profile.max_guests',
                'help' => 'help.profile.max_guests',
                'html5' => true,
                'constraints' => [
                    new Range(['min' => 1, 'max' => 20]),
                ],
            ])
            ->add('length_of_stay', CkEditorType::class, [
                'label' => 'label.profile.length_of_stay',
                'help' => 'help.profile.length_of_stay',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('i_live_with', CkEditorType::class, [
                'label' => 'label.profile.i_live_with',
                'help' => 'help.profile.i_live_with',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('please_bring', CkEditorType::class, [
                'label' => 'label.profile.please_bring',
                'help' => 'help.profile.please_bring',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('where_you_sleep', CkEditorType::class, [
                'label' => 'label.profile.where_you_sleep',
                'help' => 'help.profile.where_you_sleep',
                'required' => false,
                'image_upload' => true,
            ])
            ->add('offer_guests', CkEditorType::class, [
                'label' => 'label.profile.offer_guests',
                'help' => 'help.profile.offer_guests',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('additional_info', CkEditorType::class, [
                'label' => 'label.profile.additional_info',
                'help' => 'help.profile.additional_info',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('transport', CkEditorType::class, [
                'label' => 'label.profile.transport',
                'help' => 'help.profile.transport',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('house_rules', CkEditorType::class, [
                'label' => 'label.profile.house_rules',
                'help' => 'help.profile.house_rules',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}
