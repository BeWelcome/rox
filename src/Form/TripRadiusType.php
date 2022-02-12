<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class TripRadiusType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('radius', Select2Type::class, [
                'label' => 'trips.radius',
                'required' => false,
                'choices' => [
                    'trips.legs.exact' => 0,
                    'search.radius.5km' => 5,
                    'search.radius.10km' => 10,
                    'search.radius.20km' => 20,
                    'search.radius.50km' => 50,
                    'search.radius.100km' => 100,
/*                    'search.radius.200km' => 200,
                    'search.radius.500km' => 500,
                    'search.radius.1000km' => 1000,
                    'search.radius.2000km' => 2000,
                    'search.radius.5000km' => 5000,
*/                ],
                'attr' => [
                    'id' => 'trips_radius',
                ],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'trips';
    }
}
