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
                'label' => 'label.profile.past_trips',
                'help' => 'help.profile.past_trips',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('planned', CkEditorType::class, [
                'label' => 'label.profile.planned_trips',
                'help' => 'help.profile.planned_trips',
                'required' => false,
                'image_upload' => false,
            ])
            ->add('language', HiddenType::class)
        ;
    }
}
