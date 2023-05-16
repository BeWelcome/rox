<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SetLocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', TextType::class, [
                'required' => false,
                'label' => 'profile.set.location',
                'constraints' => [
                    new NotBlank(null, 'location.none.given'),
                ],
            ])
            ->add('name', HiddenType::class)
            ->add('geoname_id', HiddenType::class, [
                'constraints' => [
                    new NotBlank(null, 'location.none.given'),
                ],
            ])
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
        ;
    }
}
