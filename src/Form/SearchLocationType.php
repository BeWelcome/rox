<?php

namespace App\Form;

use App\Form\CustomDataClass\LocationRequest;
use App\Form\DataTransformer\LocationRequestToLocationTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SearchLocationType extends AbstractType
{
    private LocationRequestToLocationTransformer $transformer;

    public function __construct(LocationRequestToLocationTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', TextType::class, [
                'attr' => [
                    'class' => 'search-picker',
                ],
                'required' => false,
                'disabled' => $options['expired'],
                'property_path' => 'name',
                'label' => 'trip.leg.location',
            ])
            ->add('name', HiddenType::class)
            ->add('geoname_id', HiddenType::class, [
                'property_path' => 'geonameId',
            ])
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
        ;

        $builder
            ->addModelTransformer($this->transformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LocationRequest::class,
            'expired' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
