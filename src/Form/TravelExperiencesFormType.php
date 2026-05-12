<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TravelExperiencesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $textareaOptions = [
            'required' => false,
            'attr' => ['class' => 'o-input', 'rows' => 6],
        ];

        $builder
            ->add('past', TextareaType::class, $textareaOptions + [
                'label' => 'profile.past.trips',
                'help' => 'help.profile.past.trips',
            ])
            ->add('planned', TextareaType::class, $textareaOptions + [
                'label' => 'profile.planned.trips',
                'help' => 'help.profile.planned.trips',
            ])
            ->add('language', HiddenType::class)
        ;
    }
}
