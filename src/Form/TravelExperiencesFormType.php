<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class TravelExperiencesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('past', CkEditorType::class, [
                'label' => 'profile.past.trips',
                'help' => 'help.profile.past.trips',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
                'image_upload' => false,
            ])
            ->add('planned', CkEditorType::class, [
                'label' => 'profile.planned.trips',
                'help' => 'help.profile.planned.trips',
                'editor_type' => CkEditorType::EDITOR_TYPE_INLINE,
                'required' => false,
                'image_upload' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}
