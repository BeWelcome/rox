<?php

namespace App\Form;

use App\Entity\Location;
use App\Form\CustomDataClass\LocationRequest;
use App\Form\DataTransformer\LocationRequestToLocationTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
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
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'search-picker',
                ],
                'required' => false,
                'label' => 'trip.leg.location',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('geoname_id', HiddenType::class)
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
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
