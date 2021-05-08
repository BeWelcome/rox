<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TripVicinityRadiusType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('distance', Select2Type::class, [
            'choices' => [
                'trips.legs.exact' => 0,
                'search.radius.5km' => 5,
                'search.radius.10km' => 10,
                'search.radius.15km' => 15,
                'search.radius.20km' => 20,
                'search.radius.50km' => 50,
                'search.radius.100km' => 100,
                'search.radius.200km' => 200,
                'search.radius.500km' => 500,
                'search.radius.1000km' => 1000,
            ],
            'label' => 'trips.radius',
        ]);
    }
}
