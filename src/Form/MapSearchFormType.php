<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MapSearchFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('location', TextType::class, [
                'label' => 'landing.whereyougo',
                'error_bubbling' => true,
            ])
            ->add('location_geoname_id', HiddenType::class)
            ->add('location_latitude', HiddenType::class)
            ->add('location_longitude', HiddenType::class)
            ->add('updateMap', SubmitType::class, [
                'label' => 'search.find.members',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'allow_extra_fields' => true,
            'error_mapping' => [
                '.' => 'location',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'search_map';
    }
}
