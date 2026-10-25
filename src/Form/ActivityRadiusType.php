<?php

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The distance around the member's place for "Activities near me", as offered by the legacy page.
 */
class ActivityRadiusType extends AbstractType
{
    public const array DISTANCES = [0, 5, 10, 25, 50, 100, 200];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach (self::DISTANCES as $distance) {
            $choices['activities.radius.' . $distance] = $distance;
        }

        $builder
            ->add('radius', ChoiceType::class, [
                'label' => 'activitiesradiusselectlabel',
                'choices' => $choices,
            ])
        ;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'activities';
    }
}
